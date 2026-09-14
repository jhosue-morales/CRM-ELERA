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
// FILTROS DE FECHA
// ==========================================
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01'); // Primer día del mes
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d'); // Hoy

// ==========================================
// CONSULTA: VENTAS POR VENDEDOR EN EL RANGO
// ==========================================
$stmt = $pdo->prepare("SELECT asignado_a, 
    COUNT(*) AS total_oportunidades,
    SUM(CASE WHEN fase_venta = 'Cerrada ganada' THEN 1 ELSE 0 END) AS ganadas,
    SUM(CASE WHEN fase_venta = 'Cerrada perdida' THEN 1 ELSE 0 END) AS perdidas,
    ROUND(SUM(CASE WHEN fase_venta = 'Cerrada ganada' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS tasa
    FROM oportunidades 
    WHERE fecha_creacion BETWEEN ? AND ?
    GROUP BY asignado_a 
    ORDER BY ganadas DESC");
$stmt->execute([$fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59']);
$vendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales generales
$total_ganadas = 0;
$total_perdidas = 0;
foreach ($vendedores as $v) {
    $total_ganadas += $v['ganadas'];
    $total_perdidas += $v['perdidas'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>CRM | Reporte de Vendedores</title>
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

        /* FILTROS */
        .filtros-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 25px; }
        .filtros-card label { font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; }
        .filtros-card .form-control { height: 42px; border-radius: 8px; border: 1px solid #dbe2ea; }
        .btn-filtrar { background: #2563eb; border: none; border-radius: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; color: white; height: 42px; }
        .btn-filtrar:hover { background: #1d4ed8; }

        /* TARJETAS DE RESUMEN */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 28px; font-weight: 700; }
        .stat-card p { margin: 5px 0 0 0; font-size: 13px; color: #64748b; }
        .stat-card.verde h3 { color: #16a34a; }
        .stat-card.rojo h3 { color: #dc2626; }
        .stat-card.azul h3 { color: #2563eb; }

        /* TABLA */
        .table-container { background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow-x: auto; }
        .table { margin: 0; }
        .table thead th { background: #f8fafc; padding: 14px 16px; color: #64748b; font-size: 12px; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 15px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:last-child td { border-bottom: none; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
            .stats-grid { grid-template-columns: 1fr; }
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
            <h1 class="page-title">Reporte de Ventas por Vendedor</h1>
            <div class="page-subtitle">Rendimiento de cada vendedor en el período seleccionado.</div>
        </div>
    </div>

    <!-- FILTROS DE FECHA -->
    <div class="filtros-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="col-md-4">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-filtrar w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card azul">
            <h3><?php echo count($vendedores); ?></h3>
            <p>Vendedores Activos</p>
        </div>
        <div class="stat-card verde">
            <h3><?php echo $total_ganadas; ?></h3>
            <p>Ventas Ganadas</p>
        </div>
        <div class="stat-card rojo">
            <h3><?php echo $total_perdidas; ?></h3>
            <p>Ventas Perdidas</p>
        </div>
    </div>

    <!-- TABLA DE VENDEDORES -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th>Total Oportunidades</th>
                    <th>Ganadas</th>
                    <th>Perdidas</th>
                    <th>Tasa de Conversión</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($vendedores): ?>
                    <?php foreach ($vendedores as $v): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $v['asignado_a'] ?: 'Sin asignar'; ?></td>
                        <td><?php echo $v['total_oportunidades']; ?></td>
                        <td><span class="badge bg-success"><?php echo $v['ganadas']; ?></span></td>
                        <td><span class="badge bg-danger"><?php echo $v['perdidas']; ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $v['tasa']; ?>%"></div>
                                </div>
                                <span style="font-size: 13px; font-weight: 600;"><?php echo $v['tasa']; ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay datos en el período seleccionado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>