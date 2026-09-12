<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
if ($_SESSION['usuario_rol'] !== 'admin') { header('Location: dashboard.php?error=sin_permiso'); exit(); }
require_once 'database.php';

$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Usuarios - Mi CRM</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; color: #1e293b; }
        
        /* =====================================
           SIDEBAR (Copiado de tu clientes.php)
        ====================================== */
        .sidebar { position: fixed; top: 0; left: 0; width: 250px; height: 100vh; background: #ffffff; border-right: 1px solid #e5e7eb; padding: 24px 16px; display: flex; flex-direction: column; }
        .brand { display: flex; align-items: center; gap: 12px; padding: 0 10px; margin-bottom: 35px; }
        .brand-logo { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 9px; background: #2563eb; color: white; font-size: 13px; font-weight: 700; }
        .brand-name { font-size: 17px; font-weight: 700; }
        .menu-title { padding: 0 12px; margin-bottom: 10px; color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .menu { display: flex; flex-direction: column; gap: 4px; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 500; }
        .menu-item i { font-size: 18px; }
        .menu-item:hover { background: #f1f5f9; color: #1e293b; }
        .menu-item.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .menu-separator { height: 1px; background: #e5e7eb; margin: 20px 8px; }
        .sidebar-bottom { margin-top: auto; }
        .user { display: flex; align-items: center; gap: 10px; padding: 18px 10px 10px; border-top: 1px solid #e5e7eb; }
        .user-avatar { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #e2e8f0; color: #475569; font-size: 13px; font-weight: 600; }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: #94a3b8; }

        /* =====================================
           CONTENIDO
        ====================================== */
        .main { margin-left: 250px; min-height: 100vh; padding: 35px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }
        .page-subtitle { margin-top: 5px; color: #64748b; font-size: 14px; }

        /* =====================================
           TABLA
        ====================================== */
        .table-container { background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow-x: auto; }
        .table { margin: 0; }
        .table thead th { background: #f8fafc; padding: 14px 16px; color: #64748b; font-size: 12px; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 15px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:last-child td { border-bottom: none; }

        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 7px; background: white; color: #64748b; margin-left: 4px; }
        .action-btn:hover { background: #f8fafc; }
        .action-delete:hover { color: #dc2626; border-color: #fecaca; background: #fef2f2; }

        /* =====================================
           BOTÓN NUEVO
        ====================================== */
        .btn-new { background: #2563eb; border: none; border-radius: 8px; padding: 10px 16px; font-size: 14px; font-weight: 600; }

        /* =====================================
           RESPONSIVE
        ====================================== */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand { justify-content: center; padding: 0; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .user { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
        }
    </style>
</head>
<body>
<!-- =====================================
     SIDEBAR (Copiado de tu clientes.php)
====================================== -->
<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">CRM</div>
        <div class="brand-name">Mi CRM</div>
    </div>
    <div class="menu-title">Principal</div>
    <nav class="menu">
        <a href="dashboard.php" class="menu-item"><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="clientes.php" class="menu-item"><i class="bi bi-people"></i><span>Clientes</span></a>
        <a href="oportunidades.php" class="menu-item"><i class="bi bi-briefcase"></i><span>Oportunidades</span></a>
        <a href="#" class="menu-item"><i class="bi bi-file-earmark-text"></i><span>Cotizaciones</span></a>
        <a href="#" class="menu-item"><i class="bi bi-cart3"></i><span>Ventas</span></a>
    </nav>
    <div class="menu-separator"></div>
    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="menu-title">Sistema</div>
    <nav class="menu">
        <a href="#" class="menu-item"><i class="bi bi-bar-chart"></i><span>Reportes</span></a>
        <a href="usuarios.php" class="menu-item active"><i class="bi bi-people"></i><span>Usuarios</span></a>
    </nav>
    <?php endif; ?>
    <div class="sidebar-bottom">
        <div class="user">
            <div class="user-avatar"><a href="logout.php" class="text-decoration-none text-dark">CS</a></div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['usuario_rol'] ?? 'Admin'); ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- =====================================
     CONTENIDO
====================================== -->
<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestión de Usuarios</h1>
            <div class="page-subtitle">Administra los usuarios y sus roles en el sistema.</div>
        </div>
        <a href="crear_usuario.php" class="btn btn-primary btn-new text-white text-decoration-none">
            <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <div class="table-container">
        <table class="table">
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
                        <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="eliminar_usuario.php?id=<?php echo $u['id']; ?>" class="action-btn action-delete" onclick="return confirm('¿Seguro?')"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>