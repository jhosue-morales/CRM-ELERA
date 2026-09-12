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
    <title>Crear Oportunidad</title>
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-secondary text-white">Nueva Oportunidad</div>
            <div class="card-body">
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre Oportunidad</label>
                            <input type="text" name="nombre_oportunidad" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Asignado a</label>
                            <select name="asignado_a" class="form-select">
                                <option>Alonso Zapata Olmos</option>
                                <option>Diana Moran Carranza</option>
                                <option>Oscar Silva</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nombre de Contacto (Cliente)</label>
                            <select name="nombre_cliente" class="form-select" required>
                                <option value="">-- Selecciona un cliente --</option>
                                <?php foreach ($lista_clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>">
                                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fase de Venta</label>
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
                        <div class="col-md-6 mb-3">
                            <label>Número de Cotización</label>
                            <input type="text" name="numero_cotizacion" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Probabilidad (%)</label>
                            <input type="number" name="probabilidad" class="form-control" value="50" min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fecha Estimada de Cierre</label>
                            <input type="date" name="fecha_estimada_cierre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Subir Cotización</label>
                            <input type="file" name="archivo_cotizacion" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="oportunidades.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>