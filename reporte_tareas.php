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
// CONSULTAS DE TAREAS
// ==========================================

// 1. Tareas ATRASADAS (fecha pasada y no realizadas)
$stmt = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    WHERE t.fecha_realizacion < CURDATE() AND t.estado = 'Pendiente'
    ORDER BY t.fecha_realizacion ASC");
$tareas_atrasadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Tareas PARA HOY (fecha de hoy y no realizadas)
$stmt = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    WHERE t.fecha_realizacion = CURDATE() AND t.estado = 'Pendiente'
    ORDER BY t.prioridad DESC");
$tareas_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Tareas PRÓXIMAS (fecha futura y no realizadas)
$stmt = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    WHERE t.fecha_realizacion > CURDATE() AND t.estado = 'Pendiente'
    ORDER BY t.fecha_realizacion ASC
    LIMIT 15");
$tareas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Tareas REALIZADAS (últimas 10)
$stmt = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    WHERE t.estado = 'Realizada'
    ORDER BY t.fecha_realizacion DESC
    LIMIT 10");
$tareas_realizadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Resumen por usuario (tareas pendientes asignadas)
$stmt = $pdo->query("SELECT asignado_a, 
    COUNT(*) AS total,
    SUM(CASE WHEN t.estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN t.estado = 'Realizada' THEN 1 ELSE 0 END) AS realizadas
    FROM tareas_oportunidades t
    GROUP BY asignado_a
    ORDER BY pendientes DESC");
$resumen_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>CRM | Reporte de Tareas</title>
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

        /* TARJETAS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 28px; font-weight: 700; }
        .stat-card p { margin: 5px 0 0 0; font-size: 13px; color: #64748b; }
        .stat-card.rojo h3 { color: #dc2626; }
        .stat-card.naranja h3 { color: #ea580c; }
        .stat-card.azul h3 { color: #2563eb; }
        .stat-card.verde h3 { color: #16a34a; }

        /* SECCIONES DE TAREAS */
        .seccion-tareas { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .seccion-tareas h3 { margin: 0 0 15px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }

        /* ITEM DE TAREA */
        .tarea-item { padding: 12px; border-left: 4px solid #2563eb; background: #f8fafc; border-radius: 6px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .tarea-item.alta { border-left-color: #dc2626; background: #fef2f2; }
        .tarea-item.media { border-left-color: #f59e0b; background: #fffbeb; }
        .tarea-item.baja { border-left-color: #0ea5e9; background: #f0f9ff; }
        .tarea-item.atrasada { border-left-color: #dc2626; background: #fef2f2; }
        
        .tarea-info { flex-grow: 1; }
        .tarea-texto { font-size: 14px; font-weight: 500; margin-bottom: 4px; }
        .tarea-meta { font-size: 12px; color: #94a3b8; }
        .tarea-meta strong { color: #64748b; }
        
        .tarea-fecha { font-size: 12px; font-weight: 600; color: #64748b; text-align: right; }

        /* TABLA DE USUARIOS */
        .table-mini { width: 100%; border-collapse: collapse; }
        .table-mini th { text-align: left; padding: 10px 8px; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .table-mini td { padding: 12px 8px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .table-mini tr:last-child td { border-bottom: none; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
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
            <h1 class="page-title">Reporte de Tareas</h1>
            <div class="page-subtitle">Estado de las tareas de seguimiento del equipo.</div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card rojo">
            <h3><?php echo count($tareas_atrasadas); ?></h3>
            <p>Atrasadas</p>
        </div>
        <div class="stat-card naranja">
            <h3><?php echo count($tareas_hoy); ?></h3>
            <p>Para Hoy</p>
        </div>
        <div class="stat-card azul">
            <h3><?php echo count($tareas_proximas); ?></h3>
            <p>Próximas</p>
        </div>
        <div class="stat-card verde">
            <h3><?php echo count($tareas_realizadas); ?></h3>
            <p>Realizadas</p>
        </div>
    </div>

    <!-- TAREAS ATRASADAS -->
    <?php if ($tareas_atrasadas): ?>
    <div class="seccion-tareas">
        <h3><i class="bi bi-exclamation-triangle text-danger"></i> Tareas Atrasadas</h3>
        <?php foreach ($tareas_atrasadas as $t): ?>
            <div class="tarea-item atrasada">
                <div class="tarea-info">
                    <div class="tarea-texto"><?php echo $t['tarea']; ?></div>
                    <div class="tarea-meta">
                        Oportunidad: <strong><?php echo $t['nombre_oportunidad'] ?? 'Sin oportunidad'; ?></strong> | 
                        Asignada a: <strong><?php echo $t['asignado_a'] ?? $t['usuario']; ?></strong>
                    </div>
                </div>
                <div class="tarea-fecha text-danger">
                    <i class="bi bi-calendar-x"></i> <?php echo $t['fecha_realizacion']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- TAREAS PARA HOY -->
    <?php if ($tareas_hoy): ?>
    <div class="seccion-tareas">
        <h3><i class="bi bi-calendar-check text-warning"></i> Tareas para Hoy</h3>
        <?php foreach ($tareas_hoy as $t): ?>
            <?php
            $clase = 'baja';
            if ($t['prioridad'] == 'Alta') $clase = 'alta';
            if ($t['prioridad'] == 'Media') $clase = 'media';
            ?>
            <div class="tarea-item <?php echo $clase; ?>">
                <div class="tarea-info">
                    <div class="tarea-texto"><?php echo $t['tarea']; ?></div>
                    <div class="tarea-meta">
                        Prioridad: <strong><?php echo $t['prioridad']; ?></strong> | 
                        Oportunidad: <strong><?php echo $t['nombre_oportunidad'] ?? 'Sin oportunidad'; ?></strong> | 
                        Asignada a: <strong><?php echo $t['asignado_a'] ?? $t['usuario']; ?></strong>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- TAREAS PRÓXIMAS -->
    <?php if ($tareas_proximas): ?>
    <div class="seccion-tareas">
        <h3><i class="bi bi-calendar-week text-primary"></i> Tareas Próximas</h3>
        <?php foreach ($tareas_proximas as $t): ?>
            <?php
            $clase = 'baja';
            if ($t['prioridad'] == 'Alta') $clase = 'alta';
            if ($t['prioridad'] == 'Media') $clase = 'media';
            ?>
            <div class="tarea-item <?php echo $clase; ?>">
                <div class="tarea-info">
                    <div class="tarea-texto"><?php echo $t['tarea']; ?></div>
                    <div class="tarea-meta">
                        Prioridad: <strong><?php echo $t['prioridad']; ?></strong> | 
                        Oportunidad: <strong><?php echo $t['nombre_oportunidad'] ?? 'Sin oportunidad'; ?></strong> | 
                        Asignada a: <strong><?php echo $t['asignado_a'] ?? $t['usuario']; ?></strong>
                    </div>
                </div>
                <div class="tarea-fecha">
                    <i class="bi bi-calendar"></i> <?php echo $t['fecha_realizacion']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- RESUMEN POR USUARIO -->
    <div class="seccion-tareas">
        <h3><i class="bi bi-people text-info"></i> Resumen por Usuario</h3>
        <table class="table-mini">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Total Tareas</th>
                    <th>Pendientes</th>
                    <th>Realizadas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resumen_usuarios): ?>
                    <?php foreach ($resumen_usuarios as $u): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $u['asignado_a'] ?: 'Sin asignar'; ?></td>
                        <td><?php echo $u['total']; ?></td>
                        <td><span class="badge bg-warning text-dark"><?php echo $u['pendientes']; ?></span></td>
                        <td><span class="badge bg-success"><?php echo $u['realizadas']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted text-center py-3">No hay tareas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>