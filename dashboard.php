<?php
session_start();

// Si NO está logueado, lo manda al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$nombre = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CCS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" >
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>CRM | Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f6f8fb;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: #1e293b;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
            margin-bottom: 35px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
        }

        .menu-title {
            padding: 0 12px;
            margin-bottom: 10px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: .2s;
        }

        .menu-item i {
            font-size: 18px;
        }

        .menu-item:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .menu-item.active {
            background: #eff6ff;
            color: #2563eb;
            font-weight: 600;
        }

        .menu-separator {
            height: 1px;
            background: #e5e7eb;
            margin: 20px 8px;
        }

        .sidebar-bottom {
            margin-top: auto;
        }

        .user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 10px 10px;
            border-top: 1px solid #e5e7eb;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
        }

        .user-role {
            font-size: 11px;
            color: #94a3b8;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 250px;
            min-height: 100vh;
            padding: 35px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .page-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .page-subtitle {
            margin-top: 6px;
            color: #64748b;
            font-size: 14px;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #64748b;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: .2s;
        }

        .logout-btn:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* =========================
           WELCOME CARD
        ========================= */

        .welcome-card {
            position: relative;
            overflow: hidden;
            margin-bottom: 25px;
            padding: 27px 30px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .welcome-card h2 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #0f172a;
        }

        .welcome-card p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .welcome-icon {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            font-size: 28px;
        }

        /* =========================
           KPI CARDS
        ========================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            padding: 20px;
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            font-size: 18px;
        }

        .stat-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-value {
            color: #0f172a;
            font-size: 25px;
            font-weight: 700;
        }

        .stat-description {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
        }

        /* =========================
           QUICK ACCESS
        ========================= */

        .content-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }

        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            overflow: hidden;
        }

        .panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .panel-title {
            margin: 0;
            color: #1e293b;
            font-size: 14px;
            font-weight: 700;
        }

        .panel-subtitle {
            margin-top: 4px;
            color: #94a3b8;
            font-size: 11px;
        }

        .quick-links {
            padding: 10px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 11px;
            border-radius: 8px;
            color: #334155;
            text-decoration: none;
            transition: .2s;
        }

        .quick-link:hover {
            background: #f8fafc;
        }

        .quick-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 17px;
        }

        .quick-link-title {
            font-size: 13px;
            font-weight: 600;
        }

        .quick-link-description {
            margin-top: 2px;
            color: #94a3b8;
            font-size: 11px;
        }

        .quick-arrow {
            margin-left: auto;
            color: #cbd5e1;
        }

        /* =========================
           INFO PANEL
        ========================= */

        .info-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-size: 12px;
        }

        .info-value {
            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }

        .role-badge {
            padding: 5px 9px;
            border-radius: 20px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 768px) {

            .sidebar {
                width: 70px;
                padding: 24px 10px;
            }

            .brand {
                justify-content: center;
                padding: 0;
            }

            .brand-name,
            .menu-title,
            .menu-item span,
            .user-info {
                display: none;
            }

            .menu-item {
                justify-content: center;
            }

            .main {
                margin-left: 70px;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-icon {
                display: none;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->
<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">CRM</div>
        <div class="brand-name">Mi CRM</div>
    </div>
    <div class="menu-title">Principal</div>
    <nav class="menu">
        <a href="dashboard.php" class="menu-item active"><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="clientes.php" class="menu-item"><i class="bi bi-people"></i><span>Clientes</span></a>
        <a href="oportunidades.php" class="menu-item"><i class="bi bi-briefcase"></i><span>Oportunidades</span></a>
        <a href="#" class="menu-item"><i class="bi bi-file-earmark-text"></i><span>Cotizaciones</span></a>
        <a href="#" class="menu-item"></a><i class="bi bi-cart3"></i><span>Ventas</span></a>
    </nav>
    <div class="menu-separator"></div>
    <div class="menu-title">Sistema</div>
    <nav class="menu">
        <a href="#" class="menu-item"><i class="bi bi-bar-chart"></i><span>Reportes</span></a>
        <a href="#" class="menu-item"><i class="bi bi-gear"></i><span>Configuración</span></a>
    </nav>
    <div class="sidebar-bottom">
        <div class="user">
            <div class="user-avatar"><?php echo strtoupper(substr($nombre, 0, 1));?></div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($nombre);?></div>
                <div class="user-role"><?php echo htmlspecialchars($rol);?></div>
            </div>
        </div>
    </div>
</aside>
<!-- =========================
     MAIN
========================= -->
<main class="main">
    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <div class="page-subtitle">Resumen general de la actividad comercial.</div>
        </div>
        <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i>Cerrar sesión</a>
    </div>
    <!-- WELCOME -->
    <div class="welcome-card">
        <h2>Hola, <?php echo htmlspecialchars($nombre); ?> 👋</h2>
        <p>Bienvenido al sistema de gestión de clientes y oportunidades.</p>
        <div class="welcome-icon">
            <i class="bi bi-bar-chart-line"></i>
        </div>
    </div>
    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Clientes</div>
                <div class="stat-icon"><i class="bi bi-people"></i></div>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-description">Clientes registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Oportunidades</div>
                <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-description">Oportunidades activas</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Cotizaciones</div>
                <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-description">Cotizaciones registradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Ventas</div>
                <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-description">Ventas cerradas</div>
        </div>
    </div>
    <!-- CONTENT -->
    <div class="content-grid">
        <!-- ACCESOS -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Accesos rápidos</div>
                <div class="panel-subtitle">Acciones frecuentes del CRM</div>
            </div>
            <div class="quick-links">
                <a href="clientes.php" class="quick-link">
                    <div class="quick-icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="quick-link-title">Gestionar clientes</div>
                        <div class="quick-link-description">Consultar y administrar contactos.</div>
                    </div>
                    <i class="bi bi-chevron-right quick-arrow"></i>
                </a>
                <a href="crear_cliente.php" class="quick-link">
                    <div class="quick-icon"><i class="bi bi-person-plus"></i></div>
                    <div>
                        <div class="quick-link-title">Crear cliente</div>
                        <div class="quick-link-description">Registrar un nuevo contacto.</div>
                    </div>
                    <i class="bi bi-chevron-right quick-arrow"></i>
                </a>
                <a href="oportunidades.php" class="quick-link">
                    <div class="quick-icon"><i class="bi bi-briefcase"></i></div>
                    <div>
                        <div class="quick-link-title">Oportunidades</div>
                        <div class="quick-link-description">Revisar y dar seguimiento a ventas.</div>
                    </div>
                    <i class="bi bi-chevron-right quick-arrow"></i>
                </a>
                <a href="crear_oportunidad.php" class="quick-link">
                    <div class="quick-icon"><i class="bi bi-plus-circle"></i></div>
                    <div>
                        <div class="quick-link-title">Nueva oportunidad</div>
                        <div class="quick-link-description">Registrar una nueva oportunidad comercial.</div>
                    </div>
                    <i class="bi bi-chevron-right quick-arrow"></i>
                </a>
            </div>
        </div>
        <!-- INFORMACIÓN DEL USUARIO -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Mi cuenta</div>
                <div class="panel-subtitle">Información de la sesión actual</div>
            </div>
            <div class="info-body">
                <div class="info-row">
                    <span class="info-label">Usuario</span>
                    <span class="info-value"><?php echo htmlspecialchars($nombre);?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rol</span>
                    <span class="role-badge"><?php echo htmlspecialchars($rol);?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value">Sesión activa</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sistema</span>
                    <span class="info-value">Mi CRM</span>
                </div>
            </div>
        </div>
    </div>
</main>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>