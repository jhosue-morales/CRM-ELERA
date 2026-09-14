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
// CONSULTA: OPORTUNIDADES POR FASE
// ==========================================
$fases = ['Contactado', 'Analizando necesidad', 'Cotización enviada', 'Esperando respuesta', 'Negociación', 'Cerrada ganada', 'Cerrada perdida'];

$datos_fases = [];
$total_oportunidades = 0;

foreach ($fases as $fase) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oportunidades WHERE fase_venta = ?");
    $stmt->execute([$fase]);
    $cantidad = $stmt->fetchColumn();
    $datos_fases[$fase] = $cantidad;
    $total_oportunidades += $cantidad;
}

// Calcular porcentajes
$porcentajes = [];
foreach ($datos_fases as $fase => $cantidad) {
    $porcentajes[$fase] = ($total_oportunidades > 0) ? round(($cantidad / $total_oportunidades) * 100, 1) : 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>CRM | Reporte por Fases</title>
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

        /* EMBUDO */
        .embudo-container { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 25px; margin-bottom: 20px; }
        .embudo-container h3 { margin: 0 0 20px 0; font-size: 16px; font-weight: 700; }
        
        .fase-item { display: flex; align-items: center; margin-bottom: 15px; gap: 15px; }
        .fase-nombre { width: 180px; font-size: 13px; font-weight: 600; color: #334155; }
        .fase-barra-container { flex-grow: 1; height: 35px; background: #f1f5f9; border-radius: 8px; overflow: hidden; position: relative; }
        .fase-barra { height: 100%; border-radius: 8px; display: flex; align-items: center; justify-content: flex-end; padding-right: 12px; color: white; font-weight: 600; font-size: 13px; transition: width 0.5s ease; }
        .fase-cantidad { width: 50px; text-align: right; font-weight: 700; font-size: 15px; }

        /* Colores por fase */
        .barra-contactado { background: linear-gradient(90deg, #0ea5e9, #0284c7); }
        .barra-analizando { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .barra-cotizacion { background: linear-gradient(90deg, #22c55e, #16a34a); }
        .barra-esperando { background: linear-gradient(90deg, #9333ea, #7c3aed); }
        .barra-negociacion { background: linear-gradient(90deg, #ea580c, #c2410c); }
        .barra-ganada { background: linear-gradient(90deg, #16a34a, #15803d); }
        .barra-perdida { background: linear-gradient(90deg, #dc2626, #b91c1c); }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
            .stats-grid { grid-template-columns: 1fr; }
            .fase-nombre { width: 100px; font-size: 12px; }
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
            <h1 class="page-title">Oportunidades por Fase</h1>
            <div class="page-subtitle">Visualiza en qué etapa del embudo se encuentran tus oportunidades.</div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $total_oportunidades; ?></h3>
            <p>Total Oportunidades</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #16a34a;"><?php echo $datos_fases['Cerrada ganada']; ?></h3>
            <p>Ganadas</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #dc2626;"><?php echo $datos_fases['Cerrada perdida']; ?></h3>
            <p>Perdidas</p>
        </div>
    </div>

    <!-- EMBUDO DE FASES -->
    <div class="embudo-container">
        <h3>Embudo de Ventas</h3>
        
        <?php
        $clases_barras = [
            'Contactado' => 'barra-contactado',
            'Analizando necesidad' => 'barra-analizando',
            'Cotización enviada' => 'barra-cotizacion',
            'Esperando respuesta' => 'barra-esperando',
            'Negociación' => 'barra-negociacion',
            'Cerrada ganada' => 'barra-ganada',
            'Cerrada perdida' => 'barra-perdida'
        ];
        
        foreach ($datos_fases as $fase => $cantidad):
            $porcentaje = $porcentajes[$fase];
            $clase = $clases_barras[$fase];
        ?>
        <div class="fase-item">
            <div class="fase-nombre"><?php echo $fase; ?></div>
            <div class="fase-barra-container">
                <div class="fase-barra <?php echo $clase; ?>" style="width: <?php echo max($porcentaje, 8); ?>%;">
                    <?php echo $porcentaje; ?>%
                </div>
            </div>
            <div class="fase-cantidad"><?php echo $cantidad; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>