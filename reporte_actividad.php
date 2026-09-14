<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }

// Solo admin
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: dashboard.php?error=sin_permiso');
    exit();
}

require_once 'database.php';

// ==========================================
// CONSULTAS DE ACTIVIDAD POR USUARIO
// ==========================================

// 1. Clientes creados por usuario (basado en el historial)
$stmt = $pdo->query("SELECT usuario, COUNT(*) AS total 
    FROM historial_clientes 
    WHERE accion LIKE 'Contacto creado%'
    GROUP BY usuario 
    ORDER BY total DESC");
$clientes_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Oportunidades creadas por usuario (asignado_a)
$stmt = $pdo->query("SELECT asignado_a AS usuario, COUNT(*) AS total 
    FROM oportunidades 
    GROUP BY asignado_a 
    ORDER BY total DESC");
$oportunidades_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Tareas creadas por usuario
$stmt = $pdo->query("SELECT usuario, COUNT(*) AS total 
    FROM tareas_oportunidades 
    GROUP BY usuario 
    ORDER BY total DESC");
$tareas_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Tareas realizadas por usuario (asignado_a)
$stmt = $pdo->query("SELECT asignado_a AS usuario, COUNT(*) AS total 
    FROM tareas_oportunidades 
    WHERE estado = 'Realizada'
    GROUP BY asignado_a 
    ORDER BY total DESC");
$tareas_realizadas_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Comentarios creados por usuario
$stmt = $pdo->query("SELECT usuario, COUNT(*) AS total 
    FROM comentarios_clientes 
    GROUP BY usuario 
    ORDER BY total DESC");
$comentarios_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Consolidado general: todos los usuarios y sus actividades
$stmt = $pdo->query("SELECT nombre, email, rol FROM usuarios ORDER BY nombre ASC");
$todos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para cada usuario, contar sus actividades
$actividades = [];
foreach ($todos_usuarios as $u) {
    $nombre = $u['nombre'];
    
    // Clientes creados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_clientes WHERE usuario = ? AND accion LIKE 'Contacto creado%'");
    $stmt->execute([$nombre]);
    $clientes = $stmt->fetchColumn();
    
    // Oportunidades asignadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oportunidades WHERE asignado_a = ?");
    $stmt->execute([$nombre]);
    $oportunidades = $stmt->fetchColumn();
    
    // Tareas asignadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tareas_oportunidades WHERE asignado_a = ?");
    $stmt->execute([$nombre]);
    $tareas = $stmt->fetchColumn();
    
    // Tareas realizadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tareas_oportunidades WHERE asignado_a = ? AND estado = 'Realizada'");
    $stmt->execute([$nombre]);
    $tareas_ok = $stmt->fetchColumn();
    
    // Comentarios
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comentarios_clientes WHERE usuario = ?");
    $stmt->execute([$nombre]);
    $comentarios = $stmt->fetchColumn();
    
    $actividades[] = [
        'nombre' => $nombre,
        'email' => $u['email'],
        'rol' => $u['rol'],
        'clientes' => $clientes,
        'oportunidades' => $oportunidades,
        'tareas' => $tareas,
        'tareas_ok' => $tareas_ok,
        'comentarios' => $comentarios
    ];
}

// Ordenar por total de actividad (suma de todo)
usort($actividades, function($a, $b) {
    $total_a = $a['clientes'] + $a['oportunidades'] + $a['tareas'] + $a['comentarios'];
    $total_b = $b['clientes'] + $b['oportunidades'] + $b['tareas'] + $b['comentarios'];
    return $total_b - $total_a;
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>CRM | Reporte de Actividad</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; color: #1e293b; }

        /* SIDEBAR */
        .sidebar { position: fixed; top: 0; left: 0; width: 250px; height: 100vh; background: #ffffff; border-right: 1px solid #e5e7eb; padding: 24px 16px; display: flex; flex-direction: column; overflow-y: auto; scrollbar-width: none; transition: width 0.25s ease; }
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
        .sidebar-bottom { margin-top: auto; padding-top: 20px; }
        .user { display: flex; align-items: center; gap: 10px; padding: 18px 10px 10px; border-top: 1px solid #e5e7eb; }
        .user-avatar { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #e2e8f0; color: #475569; font-size: 13px; font-weight: 600; }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: #94a3b8; }

        /* CONTENIDO */
        .main { margin-left: 250px; min-height: 100vh; padding: 35px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }
        .page-subtitle { margin-top: 5px; color: #64748b; font-size: 14px; }

        /* TABLA */
        .table-container { background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow-x: auto; }
        .table { margin: 0; }
        .table thead th { background: #f8fafc; padding: 14px 16px; color: #64748b; font-size: 12px; font-weight: 600; border-bottom: 1px solid #e5e7eb; text-align: center; }
        .table tbody td { padding: 15px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: #f8fafc; }

        .badge-rol { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-admin { background: #fef2f2; color: #dc2626; }
        .badge-vendedor { background: #eff6ff; color: #2563eb; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="brand"><div class="brand-logo">CRM</div><div class="brand-name">Mi CRM</div></div>
    <div class="menu-title">Principal</div>
    <nav class="menu">
        <a href="dashboard.php" class="menu-item"><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="clientes.php" class="menu-item"><i class="bi bi-people"></i><span>Clientes</span></a>
        <a href="oportunidades.php" class="menu-item"><i class="bi bi-briefcase"></i><span>Oportunidades</span></a>
        <a href="calendario.php" class="menu-item"><i class="bi bi-calendar-event"></i><span>Calendario</span></a>
    </nav>
    <div class="menu-separator"></div>
    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="menu-title">Sistema</div>
    <nav class="menu">
        <a href="reportes.php" class="menu-item active"><i class="bi bi-bar-chart"></i><span>Reportes</span></a>
        <a href="usuarios.php" class="menu-item"><i class="bi bi-gear"></i><span>Usuarios</span></a>
    </nav>
    <?php endif; ?>
    <div class="sidebar-bottom">
        <div class="user">
            <div class="user-avatar"><a href="logout.php" class="text-decoration-none text-dark">CS</a></div>
            <div class="user-info"><div class="user-name"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></div><div class="user-role"><?php echo ucfirst($_SESSION['usuario_rol'] ?? 'Admin'); ?></div></div>
        </div>
    </div>
</aside>

<!-- CONTENIDO -->
<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Reporte de Actividad por Usuario</h1>
            <div class="page-subtitle">Resumen de la actividad de cada usuario en el sistema.</div>
        </div>
    </div>

    <!-- TABLA CONSOLIDADA -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Clientes Creados</th>
                    <th>Oportunidades Asignadas</th>
                    <th>Tareas Asignadas</th>
                    <th>Tareas Realizadas</th>
                    <th>Comentarios</th>
                    <th>Total Actividad</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($actividades): ?>
                    <?php foreach ($actividades as $a): ?>
                        <?php
                        $total = $a['clientes'] + $a['oportunidades'] + $a['tareas'] + $a['comentarios'];
                        $badge = ($a['rol'] == 'admin') ? 'badge-admin' : 'badge-vendedor';
                        ?>
                        <tr>
                            <td style="text-align: left; font-weight: 600;"><?php echo $a['nombre']; ?></td>
                            <td><span class="badge-rol <?php echo $badge; ?>"><?php echo ucfirst($a['rol']); ?></span></td>
                            <td><?php echo $a['clientes']; ?></td>
                            <td><?php echo $a['oportunidades']; ?></td>
                            <td><?php echo $a['tareas']; ?></td>
                            <td><span class="badge bg-success"><?php echo $a['tareas_ok']; ?></span></td>
                            <td><?php echo $a['comentarios']; ?></td>
                            <td><span class="badge bg-primary" style="font-size: 14px;"><?php echo $total; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>