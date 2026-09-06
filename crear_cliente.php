<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'database.php';

$tipos = $pdo->query("SELECT * FROM tipos_clientes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $telefono_empresa = $_POST['telefono_empresa'];
    $tipo = $_POST['tipo'];
    $asignado = $_POST['asignado'];
    $compras = $_POST['compras_realizadas'] ?? 0;
    $direccion = $_POST['direccion'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, telefono, telefono_empresa, tipo, asignado, compras_realizadas, direccion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $telefono, $telefono_empresa, $tipo, $asignado, $compras, $direccion]);
        
        $clienteId = $pdo->lastInsertId();
        
        // Registrar en historial (usa el nombre real de tu sesión)
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        $stmtHist = $pdo->prepare("INSERT INTO historial_clientes (cliente_id, usuario, accion) VALUES (?, ?, ?)");
        $stmtHist->execute([$clienteId, $usuario, 'Contacto creado']);
        
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
    <title>Crear Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-secondary text-white">Creación Rápida Contacto</div>
            <div class="card-body">
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido *</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Móvil</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Teléfono Empresa</label>
                            <input type="text" name="telefono_empresa" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tipo *</label>
                            <select name="tipo" class="form-select" required>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?php echo $t['nombre']; ?>"><?php echo $t['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Asignado a</label>
                            <select name="asignado" class="form-select">
                                <option>Alonso Zapata Olmos</option>
                                <option>Diana Moran Carranza</option>
                                <option>Oscar Silva</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Dirección (Factura)</label>
                            <textarea name="direccion" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Compras Realizadas</label>
                            <input type="number" name="compras_realizadas" class="form-control" value="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>