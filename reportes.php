<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }

// Solo el admin puede ver reportes
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: dashboard.php?error=sin_permiso');
    exit();
}

require_once 'database.php';

// ==========================================
// CONSULTAS PARA EL REPORTE
// ==========================================

// 1. Total de clientes
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

// 2. Total de oportunidades
$total_oportunidades = $pdo->query("SELECT COUNT(*) FROM oportunidades")->fetchColumn();

// 3. Ventas ganadas
$ventas_ganadas = $pdo->query("SELECT COUNT(*) FROM oportunidades WHERE fase_venta = 'Cerrada ganada'")->fetchColumn();

// 4. Ventas perdidas
$ventas_perdidas = $pdo->query("SELECT COUNT(*) FROM oportunidades WHERE fase_venta = 'Cerrada perdida'")->fetchColumn();

// 5. Tasa de conversión
$tasa_conversion = 0;
if ($total_oportunidades > 0) {
    $tasa_conversion = round(($ventas_ganadas / $total_oportunidades) * 100, 1);
}

// 6. Oportunidades por fase
$fases = ['Contactado', 'Analizando necesidad', 'Cotización enviada', 'Esperando respuesta', 'Negociación', 'Cerrada ganada', 'Cerrada perdida'];
$datos_fases = [];
foreach ($fases as $fase) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oportunidades WHERE fase_venta = ?");
    $stmt->execute([$fase]);
    $datos_fases[$fase] = $stmt->fetchColumn();
}

// 7. Top 5 vendedores
$top_vendedores = $pdo->query("SELECT asignado_a, 
    COUNT(*) AS total_oportunidades,
    SUM(CASE WHEN fase_venta = 'Cerrada ganada' THEN 1 ELSE 0 END) AS ganadas,
    SUM(CASE WHEN fase_venta = 'Cerrada perdida' THEN 1 ELSE 0 END) AS perdidas
    FROM oportunidades 
    GROUP BY asignado_a 
    ORDER BY ganadas DESC 
    LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// 8. Tareas pendientes (hoy en adelante)
$tareas_pendientes = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    WHERE t.fecha_realizacion >= CURDATE() 
    ORDER BY t.fecha_realizacion ASC 
    LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>CRM | Reportes</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; color: #1e293b; }

        /* =====================================
           SIDEBAR (COPIA DE TU OTRA VISTA)
        ====================================== */
        .sidebar { position: fixed; top: 0; left: 0; width: 250px; height: 100vh; background: #ffffff; border-right: 1px solid #e5e7eb; padding: 24px 16px; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; scrollbar-width: none; transition: width 0.25s ease; }
        .brand { display: flex; align-items: center; gap: 12px; padding: 0 10px; margin-bottom: 35px; }
        .sidebar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 35px; flex-shrink: 0; }
        .sidebar-header .brand { margin-bottom: 0; }
        .sidebar.collapsed .sidebar-header { flex-direction: column; justify-content: flex-start; align-items: center; gap: 12px; }
        .sidebar.collapsed .brand { margin-bottom: 0; }
        .sidebar.collapsed .sidebar-toggle { margin-left: 0; }
        .sidebar-toggle { margin-left: auto; border: none; background: transparent; color: #64748b; font-size: 20px; }
        .sidebar-toggle:hover { background: #f1f5f9; color: #1e293b; }
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
        .sidebar.collapsed { width: 70px; }
        .sidebar.collapsed .brand-name, .sidebar.collapsed .menu-title, .sidebar.collapsed .menu-item span, .sidebar.collapsed .user-info { display: none; }
        .sidebar.collapsed .brand { justify-content: center; padding: 0; }
        .sidebar.collapsed .menu-item { justify-content: center; }
        .sidebar.collapsed .user { justify-content: center; }

        /* =====================================
           CONTENIDO
        ====================================== */
        .main { margin-left: 250px; min-height: 100vh; padding: 35px; transition: margin-left 0.25s ease; }
        .main.sidebar-collapsed { margin-left: 70px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }
        .page-subtitle { margin-top: 5px; color: #64748b; font-size: 14px; }

        /* =====================================
           TARJETAS DE RESUMEN
        ====================================== */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; display: flex; align-items: center; gap: 12px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-icon.blue { background: #eff6ff; color: #2563eb; }
        .stat-icon.green { background: #dcfce7; color: #16a34a; }
        .stat-icon.red { background: #fef2f2; color: #dc2626; }
        .stat-icon.orange { background: #ffedd5; color: #ea580c; }
        .stat-icon.purple { background: #f3e8ff; color: #9333ea; }
        .stat-info h3 { margin: 0; font-size: 22px; font-weight: 700; }
        .stat-info p { margin: 0; font-size: 12px; color: #64748b; }

        /* =====================================
           GRID DE CONTENIDO
        ====================================== */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card-custom { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
        .card-custom h3 { margin: 0 0 15px 0; font-size: 16px; font-weight: 700; }

        /* TABLA TOP VENDEDORES */
        .table-mini { width: 100%; border-collapse: collapse; }
        .table-mini th { text-align: left; padding: 8px 5px; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .table-mini td { padding: 10px 5px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .table-mini tr:last-child td { border-bottom: none; }

        /* TAREAS PENDIENTES */
        .tarea-item { padding: 10px; border-left: 3px solid #2563eb; background: #f8fafc; border-radius: 5px; margin-bottom: 8px; }
        .tarea-item.alta { border-left-color: #dc2626; }
        .tarea-item.media { border-left-color: #f59e0b; }
        .tarea-item.baja { border-left-color: #0ea5e9; }
        .tarea-item .tarea-texto { font-size: 13px; font-weight: 500; }
        .tarea-item .tarea-info { font-size: 11px; color: #94a3b8; margin-top: 3px; }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { width: 70%; position: fixed; overflow-y: auto; }
            .sidebar .brand-name, .sidebar .menu-title, .sidebar .menu-item span, .sidebar .user-info { display: block; }
            .main { margin-left: 0; padding: 20px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- =====================================
     SIDEBAR (con la clase "active" en Reportes)
====================================== -->
<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-logo">CRM</div>
            <div class="brand-name">Mi CRM</div>
        </div>
        <button id="btnToggleSidebar" class="sidebar-toggle" type="button" title="Contraer menú">
            <i class="bi bi-list"></i>
        </button>
    </div>
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
    <div class="user-dropdown">
        <button class="user-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 2)); ?></div>
            <div class="user-info"><div class="user-name"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['usuario_rol'] ?? 'Admin'); ?></div>
            </div>
            <i class="bi bi-chevron-down user-chevron"></i>
        </button>

        <ul class="dropdown-menu user-menu">
            <li>
                <a class="dropdown-item text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Cerrar sesión
                </a>
            </li>
        </ul>
    </div>
    </div>
</aside>

<!-- =====================================
     CONTENIDO DEL REPORTE
====================================== -->
<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Reportes</h1>
            <div class="page-subtitle">Resumen general de la actividad del CRM.</div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div class="stat-info"><h3><?php echo $total_clientes; ?></h3><p>Clientes</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-briefcase"></i></div>
            <div class="stat-info"><h3><?php echo $total_oportunidades; ?></h3><p>Oportunidades</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info"><h3><?php echo $ventas_ganadas; ?></h3><p>Ganadas</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
            <div class="stat-info"><h3><?php echo $ventas_perdidas; ?></h3><p>Perdidas</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-info"><h3><?php echo $tasa_conversion; ?>%</h3><p>Conversión</p></div>
        </div>
    </div>

    <!-- GRID: GRÁFICO + TOP VENDEDORES -->
    <div class="content-grid">
        <div class="card-custom">
            <h3>Oportunidades por Fase</h3>
            <canvas id="graficoFases" height="120"></canvas>
        </div>
        <div class="card-custom">
            <h3>Top 5 Vendedores</h3>
            <table class="table-mini">
                <thead><tr><th>Vendedor</th><th>Ganadas</th><th>Perdidas</th></tr></thead>
                <tbody>
                    <?php if ($top_vendedores): ?>
                        <?php foreach ($top_vendedores as $v): ?>
                        <tr>
                            <td><?php echo $v['asignado_a'] ?: 'Sin asignar'; ?></td>
                            <td><span class="badge bg-success"><?php echo $v['ganadas']; ?></span></td>
                            <td><span class="badge bg-danger"><?php echo $v['perdidas']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-muted">Sin datos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAREAS PENDIENTES -->
    <div class="card-custom">
        <h3>Tareas Pendientes (Próximas)</h3>
        <?php if ($tareas_pendientes): ?>
            <?php foreach ($tareas_pendientes as $t): ?>
                <?php
                $clase = 'baja';
                if ($t['prioridad'] == 'Alta') $clase = 'alta';
                if ($t['prioridad'] == 'Media') $clase = 'media';
                ?>
                <div class="tarea-item <?php echo $clase; ?>">
                    <div class="tarea-texto"><?php echo $t['tarea']; ?></div>
                    <div class="tarea-info">
                        Oportunidad: <?php echo $t['nombre_oportunidad'] ?? 'Sin oportunidad'; ?> | 
                        Fecha: <?php echo $t['fecha_realizacion']; ?> | 
                        Asignada a: <?php echo $t['asignado_a'] ?? 'Sin asignar'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No hay tareas pendientes.</p>
        <?php endif; ?>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoFases').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($datos_fases)); ?>,
            datasets: [{
                label: 'Cantidad de Oportunidades',
                data: <?php echo json_encode(array_values($datos_fases)); ?>,
                backgroundColor: [
                    '#0ea5e9', '#f59e0b', '#22c55e', '#9333ea', '#ea580c', '#16a34a', '#dc2626'
                ],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(
            tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl)
        );
    </script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const main = document.querySelector('.main');
        const btnToggleSidebar = document.getElementById('btnToggleSidebar');
        
        btnToggleSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('sidebar-collapsed');
            const estaColapsado = sidebar.classList.contains('collapsed');
            btnToggleSidebar.title = estaColapsado
            ? 'Expandir menú'
            : 'Contraer menú';
        });
    </script>
    <script>
        const popoverTriggerList = document.querySelectorAll(
            '[data-bs-toggle="popover"]'
        );

        const popoverList = [...popoverTriggerList].map(
            popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
                trigger: 'hover focus',
                html: true,
                placement: 'right',
                container: 'body'
            })
        );
    </script>
</body>
</html>