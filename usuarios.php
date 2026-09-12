<?php
$roles_permitidos = ['admin'];
require_once 'auth.php';
require_once 'database.php';

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; }
        .main { padding: 35px; }
        .table-container { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
    </style>
</head>
<body>
    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Usuarios</h2>
            <a href="crear_usuario.php" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Nuevo Usuario
            </a>
        </div>

        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['nombre']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td>
                            <span class="badge <?php echo ($u['rol'] == 'admin') ? 'bg-danger' : 'bg-info'; ?>">
                                <?php echo ucfirst($u['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <?php if ($u['id'] != $_SESSION['usuario_id']): // No puede borrarse a sí mismo ?>
                                <a href="eliminar_usuario.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>