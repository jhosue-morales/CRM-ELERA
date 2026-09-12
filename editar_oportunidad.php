<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM oportunidades WHERE id = ?");
$stmt->execute([$id]);
$oportunidad = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultar todos los clientes para el desplegable
$stmt_clientes = $pdo->query("SELECT id, nombre, apellido FROM clientes ORDER BY nombre ASC");
$lista_clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_oportunidad = $_POST['nombre_oportunidad'];
    $asignado_a = $_POST['asignado_a'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $fase_venta = $_POST['fase_venta'];
    $fecha_estimada_cierre = $_POST['fecha_estimada_cierre'];
    $numero_cotizacion = $_POST['numero_cotizacion'];
    $probabilidad = $_POST['probabilidad'];
    $descripcion = $_POST['descripcion'];
    
    // Lógica para subir archivo (si se sube uno nuevo, reemplaza al viejo)
    $archivo_cotizacion = $oportunidad['archivo_cotizacion'];
    if (isset($_FILES['archivo_cotizacion']) && $_FILES['archivo_cotizacion']['error'] === UPLOAD_ERR_OK) {
        // Asegurar que la carpeta exista
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        $nombre_archivo = time() . '_' . $_FILES['archivo_cotizacion']['name'];
        $ruta_destino = 'uploads/' . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['archivo_cotizacion']['tmp_name'], $ruta_destino)) {
            $archivo_cotizacion = $ruta_destino;
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE oportunidades SET nombre_oportunidad=?, asignado_a=?, nombre_cliente=?, fase_venta=?, fecha_estimada_cierre=?, numero_cotizacion=?, probabilidad=?, descripcion=?, archivo_cotizacion=? WHERE id=?");
        $stmt->execute([$nombre_oportunidad, $asignado_a, $nombre_cliente, $fase_venta, $fecha_estimada_cierre, $numero_cotizacion, $probabilidad, $descripcion, $archivo_cotizacion, $id]);
        header("Location: oportunidades.php");
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

        <title>Editar Oportunidad - Mi CRM</title>

        <link
            rel="shortcut icon"
            href="/littlefavicon.ico"
            type="image/x-icon"
        >

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        >

        <style>

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: #f6f8fb;
                font-family: "Inter", "Segoe UI", sans-serif;
                color: #1e293b;
            }

            /* MAIN */
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

            /* CARD */

            .form-card {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(15, 23, 42, .03);
                overflow: hidden;
            }

            .form-header {
                padding: 22px 26px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .section-icon {
                width: 42px;
                height: 42px;
                border-radius: 10px;
                background: #eff6ff;
                color: #2563eb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .form-header h2 {
                margin: 0;
                font-size: 17px;
                font-weight: 700;
            }

            .form-header p {
                margin: 3px 0 0;
                color: #94a3b8;
                font-size: 13px;
            }

            .form-body {
                padding: 28px;
            }

            .section-title {
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 5px 0 18px;
                color: #334155;
                font-size: 13px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .4px;
            }

            .section-title i {
                color: #2563eb;
                font-size: 15px;
            }

            .form-label {
                margin-bottom: 7px;
                color: #334155;
                font-size: 13px;
                font-weight: 600;
            }

            .form-control,
            .form-select {
                min-height: 43px;
                border: 1px solid #dbe2ea;
                border-radius: 8px;
                color: #1e293b;
                font-size: 14px;
                box-shadow: none;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #93c5fd;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
            }

            textarea.form-control {
                min-height: 120px;
                resize: vertical;
            }

            .file-box {
                padding: 13px;
                border: 1px dashed #cbd5e1;
                border-radius: 8px;
                background: #f8fafc;
            }

            .current-file {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 9px;
                padding: 9px 11px;
                background: #eff6ff;
                border-radius: 7px;
                font-size: 12px;
                color: #475569;
            }

            .current-file i {
                color: #2563eb;
            }

            .current-file a {
                color: #2563eb;
                font-weight: 600;
                text-decoration: none;
            }

            .current-file a:hover {
                text-decoration: underline;
            }

            .help-text {
                margin-top: 6px;
                color: #94a3b8;
                font-size: 12px;
            }

            /* FOOTER */

            .form-footer {
                padding: 18px 28px;
                background: #f8fafc;
                border-top: 1px solid #e5e7eb;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .btn {
                min-height: 40px;
                padding: 9px 17px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
            }

            .btn-primary {
                background: #2563eb;
                border-color: #2563eb;
            }

            .btn-primary:hover {
                background: #1d4ed8;
                border-color: #1d4ed8;
            }

            .btn-light-custom {
                background: #ffffff;
                border: 1px solid #dbe2ea;
                color: #475569;
            }

            .btn-light-custom:hover {
                background: #f1f5f9;
            }

            .alert {
                border-radius: 8px;
                font-size: 13px;
            }

            /* RESPONSIVE */

            @media (max-width: 768px) {

                .sidebar {
                    width: 70px;
                    padding: 24px 10px;
                }

                .brand {
                    justify-content: center;
                    padding: 0;
                }

                .brand-name,
                .menu-title,
                .menu-item span,
                .user-info {
                    display: none;
                }

                .menu-item {
                    justify-content: center;
                }

                .main {
                    margin-left: 70px;
                    padding: 20px;
                }

                .form-body {
                    padding: 20px;
                }

                .form-footer {
                    padding: 16px 20px;
                }
            }

        </style>

    </head>
    <body>

    <!-- MAIN -->
        <main class="main">
            <div class="page-container">
                <!-- ENCABEZADO -->
        <div class="breadcrumb-custom">Oportunidades / <span>Nueva oportunidad</span></div>
        <h1 class="page-title">
            Nueva oportunidad
        </h1>
        <p class="page-description">
            Registra la información de la oportunidad para incorporarla al sistema.
        </p>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <div class="form-card">
                <div class="form-header">
                    <div class="section-icon"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <h2>Información de la oportunidad</h2>
                        <p>Actualiza los datos registrados.</p>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-body">
                        <!-- INFORMACIÓN GENERAL -->
                        <div class="section-title"><i class="bi bi-info-circle"></i>Información general</div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6"><label class="form-label">Nombre de la oportunidad</label>
                                <input type="text" name="nombre_oportunidad" value="<?php echo $oportunidad['nombre_oportunidad']; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Asignado a</label>
                                <select name="asignado_a" class="form-select">
                                    <option <?php if($oportunidad['asignado_a'] == 'Alonso Zapata Olmos') echo 'selected'; ?>>Alonso Zapata Olmos</option>
                                    <option <?php if($oportunidad['asignado_a'] == 'Diana Moran Carranza') echo 'selected'; ?>>Diana Moran Carranza</option>
                                    <option <?php if($oportunidad['asignado_a'] == 'Oscar Silva') echo 'selected'; ?>>Oscar Silva</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre de contacto / cliente</label>
                                <select name="nombre_cliente" class="form-select" required>
                                    <option value="">-- Selecciona un cliente --</option>
                                    <?php foreach ($lista_clientes as $cliente): ?>
                                        <option <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>" <?php if($oportunidad['nombre_cliente'] == $cliente['nombre'] . ' ' . $cliente['apellido']) echo 'selected'; ?>>
                                            <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fase de venta</label>
                                <select name="fase_venta" class="form-select">
                                    <option <?php if($oportunidad['fase_venta'] == 'Contactado') echo 'selected'; ?>>Contactado</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Analizando necesidad') echo 'selected'; ?>>Analizando necesidad</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Cotización enviada') echo 'selected'; ?>>Cotización enviada</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Esperando respuesta') echo 'selected'; ?>>Esperando respuesta</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Negociación') echo 'selected'; ?>>Negociación</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Cerrada ganada') echo 'selected'; ?>>Cerrada ganada</option>
                                    <option <?php if($oportunidad['fase_venta'] == 'Cerrada perdida') echo 'selected'; ?>>Cerrada perdida</option>
                                </select>
                            </div>
                        </div>
                        <!-- COTIZACIÓN -->
                        <div class="section-title"><i class="bi bi-file-earmark-text"></i>Cotización y cierre</div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Número de cotización</label>
                                <input type="text" name="numero_cotizacion" value="<?php echo $oportunidad['numero_cotizacion']; ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Probabilidad de cierre </label>
                                <div class="input-group">
                                    <input type="number" name="probabilidad" value="<?php echo $oportunidad['probabilidad']; ?>" class="form-control" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha estimada de cierre</label>
                                <input type="date" name="fecha_estimada_cierre" value="<?php echo $oportunidad['fecha_estimada_cierre']; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cotización adjunta</label>
                                <div class="file-box">
                                    <input type="file" name="archivo_cotizacion" class="form-control">
                                    <?php if (!empty($oportunidad['archivo_cotizacion'])): ?>
                                        <div class="current-file"><i class="bi bi-file-earmark-check"></i> <span>Archivo actual:</span >
                                            <a href="<?php echo $oportunidad['archivo_cotizacion']; ?>" target="_blank">Ver archivo</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="help-text"> Actualmente no hay una cotización adjunta.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- DETALLE -->
                        <div class="section-title"><i class="bi bi-card-text"></i> Detalle</div>
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" placeholder="Describe la necesidad del cliente o información relevante."><?php echo htmlspecialchars($oportunidad['descripcion']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer">
                        <a href="oportunidades.php" class="btn btn-light-custom">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
            </div>
        </main>
    </body>
</html>