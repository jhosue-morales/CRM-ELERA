<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'database.php';
$usuarios = $pdo->query("SELECT nombre FROM usuarios ORDER BY nombre ASC")->fetchAll();

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
$tipos = $pdo->query("SELECT * FROM tipos_clientes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $telefono_empresa = $_POST['telefono_empresa'];
    $tipo = $_POST['tipo'];
    $asignado = $_POST['asignado'];
    $compras = $_POST['compras_realizadas'];
    $direccion = $_POST['direccion'];
    $comentario = $_POST['comentario'] ?? '';
    
        try {
        // --- GUARDAR EL NOMBRE VIEJO ANTES DE ACTUALIZAR ---
        $nombre_viejo = $cliente['nombre'] . ' ' . $cliente['apellido'];
        $nombre_nuevo = $nombre . ' ' . $apellido;
        // ---------------------------------------------------

        // Comparamos los datos viejos con los nuevos para saber qué cambió
        $cambios = [];
        if ($cliente['nombre'] != $nombre) $cambios[] = 'nombre';
        if ($cliente['apellido'] != $apellido) $cambios[] = 'apellido';
        if ($cliente['telefono'] != $telefono) $cambios[] = 'teléfono';
        if ($cliente['tipo'] != $tipo) $cambios[] = 'tipo';
        if ($cliente['asignado'] != $asignado) $cambios[] = 'asignado';
        if ($cliente['compras_realizadas'] != $compras) $cambios[] = 'compras';
        if ($cliente['direccion'] != $direccion) $cambios[] = 'dirección';
        if ($cliente['comentario'] != $comentario) $cambios[] = 'comentario';
        
        $accion = 'Contacto editado';
        if (count($cambios) > 0) {
            $accion = 'Contacto editado: Cambió ' . implode(', ', $cambios);
        }
        
        // 1. Actualizamos la tabla CLIENTES
        $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=?, telefono_empresa=?, tipo=?, asignado=?, compras_realizadas=?, direccion=?, comentario=? WHERE id=?");
        $stmt->execute([$nombre, $apellido, $telefono, $telefono_empresa, $tipo, $asignado, $compras, $direccion, $comentario, $id]);

        // 2. Actualizamos la tabla OPORTUNIDADES (con el nombre viejo y nuevo guardados)
        $stmt_op = $pdo->prepare("UPDATE oportunidades SET nombre_cliente = ? WHERE nombre_cliente = ?");
        $stmt_op->execute([$nombre_nuevo, $nombre_viejo]);
        
        // 3. Registrar en historial
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        $stmtHist = $pdo->prepare("INSERT INTO historial_clientes (cliente_id, usuario, accion) VALUES (?, ?, ?)");
        $stmtHist->execute([$id, $usuario, $accion]);
        
        header("Location: clientes.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRM | Editar Cliente</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-secondary text-white">Editar Contacto</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?php echo $cliente['nombre']; ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido *</label>
                            <input type="text" name="apellido" value="<?php echo $cliente['apellido']; ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Móvil</label>
                            <input type="text" name="telefono" value="<?php echo $cliente['telefono']; ?>" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Teléfono Empresa</label>
                            <input type="text" name="telefono_empresa" value="<?php echo $cliente['telefono_empresa']; ?>" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tipo *</label>
                            <div class="input-group">
                                <select name="tipo" class="form-select">
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?php echo $t['nombre']; ?>" <?php if($t['nombre'] == $cliente['tipo']) echo 'selected'; ?>><?php echo $t['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTipo">+</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asignado a</label>
                            <select name="asignado" class="form-select">
                                <option value="">-- Selecciona un usuario --</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo $u['nombre']; ?>" <?php if($cliente['asignado'] == $u['nombre']) echo 'selected'; ?>>
                                        <?php echo $u['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Dirección (Factura)</label>
                            <textarea name="direccion" class="form-control"><?php echo $cliente['direccion']; ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Compras Realizadas</label>
                            <input type="number" name="compras_realizadas" value="<?php echo $cliente['compras_realizadas']; ?>" class="form-control">
                        </div>
                        
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para agregar nuevo tipo -->
    <div class="modal fade" id="modalTipo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="agregar_tipo.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar nuevo tipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="nuevo_tipo" class="form-control" placeholder="Ej: Cliente VIP" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>