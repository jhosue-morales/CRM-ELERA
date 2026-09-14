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
// CONSULTAS DE TENDENCIAS
// ==========================================

// 1. Oportunidades creadas por mes (últimos 12 meses)
$stmt = $pdo->query("SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes, 
    COUNT(*) AS creadas,
    SUM(CASE WHEN fase_venta = 'Cerrada ganada' THEN 1 ELSE 0 END) AS ganadas,
    SUM(CASE WHEN fase_venta = 'Cerrada perdida' THEN 1 ELSE 0 END) AS perdidas
    FROM oportunidades 
    WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes
    ORDER BY mes ASC");
$tendencias_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para el gráfico
$meses_labels = [];
$meses_creadas = [];
$meses_ganadas = [];
$meses_perdidas = [];
foreach ($tendencias_mes as $t) {
    $meses_labels[] = $t['mes'];
    $meses_creadas[] = $t['creadas'];
    $meses_ganadas[] = $t['ganadas'];
    $meses_perdidas[] = $t['perdidas'];
}

// 2. Tasa de conversión general
$total_op = $pdo->query("SELECT COUNT(*) FROM oportunidades")->fetchColumn();
$total_gan = $pdo->query("SELECT COUNT(*) FROM oportunidades WHERE fase_venta = 'Cerrada ganada'")->fetchColumn();
$tasa_global = ($total_op > 0) ? round(($total_gan / $total_op) * 100, 1) : 0;

// 3. Tasa de conversión del mes actual
$op_mes = $pdo->query("SELECT COUNT(*) FROM oportunidades WHERE MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())")->fetchColumn();
$gan_mes = $pdo->query("SELECT COUNT(*) FROM oportunidades WHERE MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE()) AND fase_venta = 'Cerrada ganada'")->fetchColumn();
$tasa_mes = ($op_mes > 0) ? round(($gan_mes / $op_mes) * 100, 1) : 0;

// 4. Promedio de oportunidades por mes
$promedio_mes = 0;
if (count($tendencias_mes) > 0) {
    $suma = array_sum($meses_creadas);
    $promedio_mes = round($suma / count($tendencias_mes), 1);
}

// 5. Mejor mes (con más ganadas)
$mejor_mes = null;
$max_ganadas = 0;
foreach ($tendencias_mes as $t) {
    if ($t['ganadas'] > $max_ganadas) {
        $max_ganadas = $t['ganadas'];
        $mejor_mes = $t['mes'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>CRM | Análisis de Tendencias</title>
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
        .stat-card.azul h3 { color: #2563eb; }
        .stat-card.verde h3 { color: #16a34a; }
        .stat-card.naranja h3 { color: #ea580c; }
        .stat-card.morado h3 { color: #9333ea; }

        /* CARD GRÁFICO */
        .card-custom { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .card-custom h3 { margin: 0 0 15px 0; font-size: 16px; font-weight: 700; }

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
            <h1 class="page-title">Análisis de Tendencias</h1>
            <div class="page-subtitle">Evolución del negocio en los últimos 12 meses.</div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card azul">
            <h3><?php echo $tasa_global; ?>%</h3>
            <p>Tasa Conversión Global</p>
        </div>
        <div class="stat-card verde">
            <h3><?php echo $tasa_mes; ?>%</h3>
            <p>Tasa Conversión Este Mes</p>
        </div>
        <div class="stat-card naranja">
            <h3><?php echo $promedio_mes; ?></h3>
            <p>Promedio Oportunidades/Mes</p>
        </div>
        <div class="stat-card morado">
            <h3><?php echo $mejor_mes ?: 'N/A'; ?></h3>
            <p>Mejor Mes (Ganadas)</p>
        </div>
    </div>

    <!-- GRÁFICO DE TENDENCIAS -->
    <div class="card-custom">
        <h3>Evolución de Oportunidades (Últimos 12 Meses)</h3>
        <canvas id="graficoTendencias" height="100"></canvas>
    </div>

    <!-- TABLA DE DATOS MENSUALES -->
    <div class="card-custom">
        <h3>Detalle Mensual</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Creadas</th>
                    <th>Ganadas</th>
                    <th>Perdidas</th>
                    <th>Tasa Conversión</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tendencias_mes): ?>
                    <?php foreach ($tendencias_mes as $t): ?>
                        <?php
                        $tasa = ($t['creadas'] > 0) ? round(($t['ganadas'] / $t['creadas']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo $t['mes']; ?></td>
                            <td><?php echo $t['creadas']; ?></td>
                            <td><span class="badge bg-success"><?php echo $t['ganadas']; ?></span></td>
                            <td><span class="badge bg-danger"><?php echo $t['perdidas']; ?></span></td>
                            <td><?php echo $tasa; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay datos suficientes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoTendencias').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses_labels); ?>,
            datasets: [
                {
                    label: 'Creadas',
                    data: <?php echo json_encode($meses_creadas); ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Ganadas',
                    data: <?php echo json_encode($meses_ganadas); ?>,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Perdidas',
                    data: <?php echo json_encode($meses_perdidas); ?>,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>