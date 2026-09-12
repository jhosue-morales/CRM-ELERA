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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CCS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <!-- Bootstrap icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" >
        <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
        <title>CRM | Actualizar Cliente</title> 
        <style>

            * {box-sizing: border-box;}

            body {
                margin: 0;
                background: #f6f8fb;
                font-family: "Segoe UI", Arial, sans-serif;
                color: #1e293b;
            }

            .main {
                min-height: 100vh;
                padding: 40px 50px;
            }

            .page-container {
                max-width: 1150px;
                margin: auto;
            }
            .breadcrumb-custom {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 6px;
            }
            .breadcrumb-custom span {
                color: #64748b;
            }
            .page-title {
                margin: 0;
                font-size: 26px;
                font-weight: 700;
                color: #0f172a;
            }
            .page-description {
                margin-top: 7px;
                margin-bottom: 28px;
                color: #64748b;
                font-size: 14px;
            }

            .form-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 3px 12px rgba(15, 23, 42, .04);
                overflow: hidden;
            }

            .form-content {
                padding: 30px;
            }

            .section {
                margin-bottom: 30px;
            }

            .section:last-child {
                margin-bottom: 0;
            }

            .section-header {
                display: flex;
                align-items: center;
                gap: 11px;
                margin-bottom: 22px;
            }

            .section-icon {
                width: 36px;
                height: 36px;

                display: flex;
                align-items: center;
                justify-content: center;

                background: #eff6ff;
                color: #2563eb;

                border-radius: 8px;

                font-size: 17px;
            }

            .section-title {
                margin: 0;
                font-size: 15px;
                font-weight: 650;
                color: #1e293b;
            }

            .section-description {
                margin: 2px 0 0;
                font-size: 12px;
                color: #94a3b8;
            }

            .form-label {
                margin-bottom: 7px;

                color: #334155;

                font-size: 13px;
                font-weight: 600;
            }

            .required {
                color: #ef4444;
            }

            .form-control,
            .form-select {
                height: 44px;

                border: 1px solid #dbe2ea;
                border-radius: 8px;

                font-size: 14px;

                color: #1e293b;

                transition: .2s;
            }

            textarea.form-control {
                height: auto;
                min-height: 100px;
                resize: vertical;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #2563eb;

                box-shadow:
                    0 0 0 3px rgba(37, 99, 235, .08);
            }

            .input-group .form-select {
                border-radius: 8px 0 0 8px;
            }

            .input-group .btn {
                width: 46px;
                border-radius: 0 8px 8px 0;
            }

            .comment-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 20px;
            }

            .form-footer {
                display: flex;
                justify-content: flex-end;
                gap: 10px;

                padding: 20px 30px;

                border-top: 1px solid #e2e8f0;

                background: #fafbfc;
            }

            .btn-cancel {
                height: 40px;

                padding: 0 18px;

                border: 1px solid #dbe2ea;
                border-radius: 7px;

                background: white;
                color: #64748b;

                font-size: 13px;
                font-weight: 600;
            }

            .btn-cancel:hover {
                background: #f1f5f9;
            }

            .btn-update {
                height: 40px;

                padding: 0 20px;

                border: none;
                border-radius: 7px;

                background: #2563eb;
                color: white;

                font-size: 13px;
                font-weight: 600;

                transition: .2s;
            }

            .btn-update:hover {
                background: #1d4ed8;
                transform: translateY(-1px);
            }

            .modal-content {
                border: none;
                border-radius: 12px;
                box-shadow: 0 15px 40px rgba(0,0,0,.15);
            }

            .modal-header {
                border-bottom: 1px solid #e5e7eb;
            }

            .modal-title {
                font-size: 16px;
                font-weight: 600;
            }

            @media (max-width: 768px) {
                .main {
                    padding: 25px 18px;
                }

                .form-content {
                    padding: 22px;
                }

                .form-footer {
                    padding: 18px 22px;
                }
            }
        </style>
    </head>

    <body>
        <main class="main">
            <div class="page-container">
                <!-- ENCABEZADO -->
                <div class="breadcrumb-custom">
                    Clientes / <span>Actualizar Cliente</span>
                </div>
                <h1 class="page-title">
                    Actualizar cliente
                </h1>
                <p class="page-description">
                    Modifica la información registrada del cliente.
                </p>
                
                <!-- FORMULARIO -->
                <div class="form-card">
                    <div class="form-content">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="formCliente" >
                            <!-- INFORMACIÓN -->
                            <div class="section">
                                <div class="section-header">
                                    <div class="section-icon"><i class="bi bi-person"></i></div>
                                    <div>
                                        <h2 class="section-title">Información del cliente</h2>
                                        <p class="section-description">Datos principales del contacto.</p>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <!-- NOMBRE -->
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" value="<?php echo $cliente['nombre']; ?>" class="form-control" class="form-control" required>
                                    </div>
                                    <!-- APELLIDO -->
                                    <div class="col-md-6">
                                        <label class="form-label">Apellido <span class="required">*</span></label>
                                        <input type="text" name="apellido" value="<?php echo $cliente['apellido']; ?>" class="form-control" required>
                                    </div>
                                    <!-- MÓVIL -->
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono Personal</label>
                                        <input type="text" name="telefono" value="<?php echo $cliente['telefono']; ?>" class="form-control">
                                    </div>
                                    <!-- TIPO -->
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo <span class="required">*</span></label>
                                        <div class="input-group">
                                            <select name="tipo" class="form-select" required>
                                                <?php foreach ($tipos as $t): ?>
                                                    <option value="<?php echo $t['nombre']; ?>" <?php if($t['nombre'] == $cliente['tipo']) echo 'selected'; ?>><?php echo $t['nombre']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTipo">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </div> 
                                    <!-- ASIGNADO -->
                                    <div class="col-md-6">
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
                                    <!-- DIRECCIÓN -->
                                    <div class="col-md-8">
                                        <label class="form-label">Dirección de facturación</label>
                                        <textarea name="direccion" class="form-control"><?php echo $cliente['direccion']; ?></textarea>
                                    </div>
                                    <!-- COMPRAS -->
                                    <div class="col-md-4">
                                        <label class="form-label">Compras realizadas</label>
                                        <input type="number" name="compras_realizadas" value="<?php echo $cliente['compras_realizadas']; ?>" class="form-control" min="0">
                                    </div>
                                </div>
                            </div>
                            <!-- BOTONES -->
                            <div class="form-footer">
                                <a href="clientes.php" class="btn btn-cancel">Cancelar</a>
                                <button type="submit" form="formCliente" class="btn-update"> <i class="bi bi-check-lg me-1"></i> Actualizar cliente </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    <!-- ========================== MODAL TIPO =========================== -->
        <div class="modal fade" id="modalTipo" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="agregar_tipo.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar nuevo tipo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Nombre del tipo</label>
                            <input type="text" name="nuevo_tipo" class="form-control" placeholder="Ej. Cliente VIP" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Agregar tipo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>