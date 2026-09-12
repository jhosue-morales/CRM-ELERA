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
    <title>Editar Oportunidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-secondary text-white">Editar Oportunidad</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre Oportunidad</label>
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
                        <div class="col-md-6 mb-3">
                            <label>Nombre de Contacto (Cliente)</label>
                            <select name="nombre_cliente" class="form-select" required>
                                <option value="">-- Selecciona un cliente --</option>
                                <?php foreach ($lista_clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>" <?php if($oportunidad['nombre_cliente'] == $cliente['nombre'] . ' ' . $cliente['apellido']) echo 'selected'; ?>>
                                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fase de Venta</label>
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
                        <div class="col-md-6 mb-3">
                            <label>Número de Cotización</label>
                            <input type="text" name="numero_cotizacion" value="<?php echo $oportunidad['numero_cotizacion']; ?>" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Probabilidad (%)</label>
                            <input type="number" name="probabilidad" value="<?php echo $oportunidad['probabilidad']; ?>" class="form-control" min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fecha Estimada de Cierre</label>
                            <input type="date" name="fecha_estimada_cierre" value="<?php echo $oportunidad['fecha_estimada_cierre']; ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Subir Cotización</label>
                            <input type="file" name="archivo_cotizacion" class="form-control">
                            <?php if (!empty($oportunidad['archivo_cotizacion'])): ?>
                                <small class="text-muted d-block mt-1">Archivo actual: <a href="<?php echo $oportunidad['archivo_cotizacion']; ?>" target="_blank">Descargar</a></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control"><?php echo $oportunidad['descripcion']; ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="oportunidades.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>