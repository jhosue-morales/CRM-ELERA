<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'database.php';

$mensaje = '';
if (isset($_GET['eliminado'])) {
    $mensaje = '<div class="alert alert-success">Cliente eliminado.</div>';
}

$stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .modal-dialog { max-width: 800px; }
        .sidebar-right { border-left: 1px solid #dee2e6; background: #f8f9fa; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>CONTACTOS > Todos</h4>
        <div>
            <a href="crear_cliente.php" class="btn btn-success">+ Crear Cliente</a>
        </div>
    </div>
    <?php echo $mensaje; ?>
    
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Asignado a</th>
                        <th>Compras Realizadas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo $cliente['nombre']; ?></td>
                        <td><?php echo $cliente['apellido']; ?></td>
                        <td><?php echo $cliente['telefono']; ?></td>
                        <td><?php echo $cliente['tipo']; ?></td>
                        <td><?php echo $cliente['asignado']; ?></td>
                        <td><?php echo $cliente['compras_realizadas']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#vista<?php echo $cliente['id']; ?>">👁️</button>
                            <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-light border">✏️</a>
                            <a href="eliminar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro?')">🗑️</a>
                        </td>
                    </tr>

                    <!-- Modal Vista Rápida con Historial -->
                    <div class="modal fade" id="vista<?php echo $cliente['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> <?php echo $cliente['nombre']; ?></p>
                                        <p><strong>Apellido:</strong> <?php echo $cliente['apellido']; ?></p>
                                        <p><strong>Asignado a:</strong> <?php echo $cliente['asignado']; ?></p>
                                        <p><strong>Comentario:</strong> <?php echo $cliente['comentario'] ?? 'Sin comentarios'; ?></p>
                                    </div>
                                    <div class="col-md-6 sidebar-right p-3">
                                        <h6>Actualizaciones</h6>
                                        <?php
                                        $stmtHist = $pdo->prepare("SELECT * FROM historial_clientes WHERE cliente_id = ? ORDER BY fecha DESC");
                                        $stmtHist->execute([$cliente['id']]);
                                        $historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
                                        if ($historial): ?>
                                            <?php foreach ($historial as $h): ?>
                                                <div class="small mb-2">
                                                    <strong><?php echo $h['usuario']; ?></strong><br>
                                                    <?php echo $h['accion']; ?><br>
                                                    <span class="text-muted"><?php echo $h['fecha']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted small">Sin actualizaciones.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>