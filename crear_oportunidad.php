<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

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
    
    // Lógica para subir archivo
    $archivo_cotizacion = '';
    if (isset($_FILES['archivo_cotizacion']) && $_FILES['archivo_cotizacion']['error'] === UPLOAD_ERR_OK) {
        // Asegurar que la carpeta exista antes de subir
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
        $stmt = $pdo->prepare("INSERT INTO oportunidades (nombre_oportunidad, asignado_a, nombre_cliente, fase_venta, fecha_estimada_cierre, numero_cotizacion, probabilidad, descripcion, archivo_cotizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre_oportunidad, $asignado_a, $nombre_cliente, $fase_venta, $fecha_estimada_cierre, $numero_cotizacion, $probabilidad, $descripcion, $archivo_cotizacion]);
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
        <!-- Bootstrap CCS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <!-- Bootstrap icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" >
        <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
        <title>CRM | Nueva Oportunidad</title> 
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

            /* ========================= MAIN ========================= */
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

            /* ========================= FORM CARD ========================= */

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

            .input-group-text {
                background: #f8fafc;
                border: 1px solid #dbe2ea;
                color: #64748b;
                border-radius: 8px 0 0 8px;
            }

            .input-group .form-control {
                border-radius: 0 8px 8px 0;
            }

            .file-box {
                padding: 13px;
                border: 1px dashed #cbd5e1;
                border-radius: 8px;
                background: #f8fafc;
            }

            .file-box input {
                background: #ffffff;
            }

            .help-text {
                margin-top: 6px;
                color: #94a3b8;
                font-size: 12px;
            }

            /* ========================= FOOTER ========================= */

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

            /* ========================= RESPONSIVE ========================= */

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
    <!-- CONTENIDO -->
        <main class="main">
            <div class="page-container">
                <!-- ENCABEZADO -->
                <div class="breadcrumb-custom">
                    Oportunidades / <span>Nueva oportunidad</span>
                </div>
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
                        <div class="section-icon"><i class="bi bi-briefcase"></i></div>
                        <div>
                            <h2>Información de la oportunidad</h2>
                            <p>Completa los datos principales del proceso de venta.</p>
                        </div>
                    </div>


                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-body">
                            <!-- INFORMACIÓN GENERAL -->
                            <div class="section-title"><i class="bi bi-info-circle"></i>Información general</div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre de la oportunidad</label>
                                    <input type="text" name="nombre_oportunidad" class="form-control" placeholder="Ej. Implementación de cámaras" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Asignado a</label>
                                    <select name="asignado_a" class="form-select">
                                        <option>Alonso Zapata Olmos</option>
                                        <option>Diana Moran Carranza</option>
                                        <option>Oscar Silva</option>
                                    </select> 
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre de contacto / cliente</label>
                                    <select name="nombre_cliente" class="form-select" required>
                                        <option value="">-- Selecciona un cliente --</option>
                                        <?php foreach ($lista_clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>">
                                                <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fase de venta</label>
                                    <select name="fase_venta" class="form-select">
                                        <option>Contactado</option>
                                        <option>Analizando necesidad</option>
                                        <option>Cotización enviada</option>
                                        <option>Esperando respuesta</option>
                                        <option>Negociación</option>
                                        <option>Cerrada ganada</option>
                                        <option>Cerrada perdida</option>
                                    </select>
                                </div>
                            </div>
                            <!-- COTIZACIÓN -->
                            <div class="section-title"><i class="bi bi-file-earmark-text"></i>Cotización y cierre</div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Número de cotización</label>
                                    <input type="text" name="numero_cotizacion" class="form-control" placeholder="Ej. COT-2026-001">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Probabilidad de cierre</label>
                                    <div class="input-group">
                                        <input type="number" name="probabilidad" class="form-control" value="50" min="0" max="100">                        
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha estimada de cierre</label>
                                    <input type="date" name="fecha_estimada_cierre" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cotización adjunta</label>
                                    <div class="file-box">
                                        <input type="file" name="archivo_cotizacion" class="form-control">
                                        <div class="help-text">Puedes adjuntar la cotización enviada al cliente.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- DESCRIPCIÓN -->
                            <div class="section-title">
                                <i class="bi bi-card-text"></i>
                                Detalle
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="descripcion" class="form-control" placeholder="Describe la necesidad del cliente, productos solicitados, información relevante, etc."></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- FOOTER -->
                        <div class="form-footer">
                            <a href="oportunidades.php" class="btn btn-light-custom">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar oportunidad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </body>
</html>