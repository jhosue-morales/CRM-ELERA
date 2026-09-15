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
// CONSULTAS PARA EL REPORTE
// ==========================================

// 1. Total de clientes
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

// 2. Clientes nuevos este mes
$clientes_mes = $pdo->query("SELECT COUNT(*) FROM clientes WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE())")->fetchColumn();

// 3. Clientes nuevos este año
$clientes_anio = $pdo->query("SELECT COUNT(*) FROM clientes WHERE YEAR(creado_en) = YEAR(CURDATE())")->fetchColumn();

// 4. Clientes con ventas ganadas (los más valiosos)
$stmt = $pdo->query("SELECT c.id, c.nombre, c.apellido, c.tipo, c.asignado,
    (SELECT COUNT(*) FROM oportunidades o WHERE o.nombre_cliente = CONCAT(c.nombre, ' ', c.apellido) AND o.fase_venta = 'Cerrada ganada') AS ganadas
    FROM clientes c 
    HAVING ganadas > 0
    ORDER BY ganadas DESC
    LIMIT 10");
$top_clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Clientes por tipo
$stmt = $pdo->query("SELECT tipo, COUNT(*) AS cantidad FROM clientes GROUP BY tipo ORDER BY cantidad DESC");
$clientes_por_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Clientes por mes (últimos 6 meses)
$stmt = $pdo->query("SELECT DATE_FORMAT(creado_en, '%Y-%m') AS mes, COUNT(*) AS cantidad 
    FROM clientes 
    WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC");
$clientes_por_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para el gráfico de meses
$meses_labels = [];
$meses_data = [];
foreach ($clientes_por_mes as $m) {
    $meses_labels[] = $m['mes'];
    $meses_data[] = $m['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>CRM | Reporte de Clientes</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 28px; font-weight: 700; color: #2563eb; }
        .stat-card p { margin: 5px 0 0 0; font-size: 13px; color: #64748b; }
        .stat-card.verde h3 { color: #16a34a; }
        .stat-card.naranja h3 { color: #ea580c; }

        /* GRID CONTENIDO */
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card-custom { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
        .card-custom h3 { margin: 0 0 15px 0; font-size: 16px; font-weight: 700; }

        /* TABLA */
        .table-mini { width: 100%; border-collapse: collapse; }
        .table-mini th { text-align: left; padding: 10px 8px; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .table-mini td { padding: 12px 8px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .table-mini tr:last-child td { border-bottom: none; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
            .stats-grid, .content-grid { grid-template-columns: 1fr; }
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
            <h1 class="page-title">Reporte de Clientes</h1>
            <div class="page-subtitle">Crecimiento y valor de la cartera de clientes.</div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $total_clientes; ?></h3>
            <p>Total Clientes</p>
        </div>
        <div class="stat-card verde">
            <h3><?php echo $clientes_mes; ?></h3>
            <p>Nuevos este Mes</p>
        </div>
        <div class="stat-card naranja">
            <h3><?php echo $clientes_anio; ?></h3>
            <p>Nuevos este Año</p>
        </div>
    </div>

    <!-- GRID: GRÁFICO + POR TIPO -->
    <div class="content-grid">
        <div class="card-custom">
            <h3>Crecimiento de Clientes (Últimos 6 Meses)</h3>
            <canvas id="graficoClientes" height="150"></canvas>
        </div>
        <div class="card-custom">
            <h3>Clientes por Tipo</h3>
            <table class="table-mini">
                <thead><tr><th>Tipo</th><th>Cantidad</th></tr></thead>
                <tbody>
                    <?php if ($clientes_por_tipo): ?>
                        <?php foreach ($clientes_por_tipo as $t): ?>
                        <tr>
                            <td><?php echo $t['tipo']; ?></td>
                            <td><span class="badge bg-primary"><?php echo $t['cantidad']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" class="text-muted">Sin datos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TOP CLIENTES -->
    <div class="card-custom">
        <h3>Top 10 Clientes (con más ventas ganadas)</h3>
        <table class="table-mini">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Asignado a</th>
                    <th>Ventas Ganadas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($top_clientes): ?>
                    <?php foreach ($top_clientes as $c): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $c['nombre'] . ' ' . $c['apellido']; ?></td>
                        <td><?php echo $c['tipo']; ?></td>
                        <td><?php echo $c['asignado']; ?></td>
                        <td><span class="badge bg-success"><?php echo $c['ganadas']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No hay clientes con ventas ganadas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoClientes').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses_labels); ?>,
            datasets: [{
                label: 'Clientes Nuevos',
                data: <?php echo json_encode($meses_data); ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#2563eb',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>