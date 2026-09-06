<?php
session_start();

// Si NO está logueado, lo manda al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$nombre = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];


require_once 'database.php';

$stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .table thead th { background-color: #f8f9fa; font-size: 0.9rem; color: #555; }
        .cursor-pointer { cursor: pointer; }
        .sidebar-right { border-left: 1px solid #dee2e6; background: white; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>CONTACTOS > Todos</h4>
            <button class="btn btn-secondary">Personalizar</button>
        </div>

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
                                <!-- Botón del Ojito (Vista Rápida) -->
                                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#vistaCliente<?php echo $cliente['id']; ?>">
                                    👁️
                                </button>
                            </td>
                        </tr>

                        <!-- MODAL / VISTA RÁPIDA (El Ojito) -->
                        <div class="modal fade" id="vistaCliente<?php echo $cliente['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Columna izquierda: Información -->
                                            <div class="col-md-6">
                                                <p><strong>Nombre:</strong> <?php echo $cliente['nombre']; ?></p>
                                                <p><strong>Apellido:</strong> <?php echo $cliente['apellido']; ?></p>
                                                <p><strong>Teléfono:</strong> <?php echo $cliente['telefono']; ?></p>
                                                <p><strong>Asignado a:</strong> <?php echo $cliente['asignado']; ?></p>
                                            </div>
                                            <!-- Columna derecha: Actualizaciones -->
                                            <div class="col-md-6 sidebar-right p-3">
                                                <h6 class="border-bottom pb-2">Actualizaciones</h6>
                                                <div class="small text-muted">
                                                    <p class="mb-2">📝 <strong>Cliente creado</strong></p>
                                                    <p class="mb-2">🔗 <strong>Contacto asignado</strong></p>
                                                    <p class="mb-0">🛒 <strong>Compras registradas:</strong> <?php echo $cliente['compras_realizadas']; ?></p>
                                                </div>
                                            </div>
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