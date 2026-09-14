<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'database.php';

$mensaje = '';
if (isset($_GET['eliminado'])) {
    $mensaje = '<div class="alert alert-success">Cliente eliminado.</div>';
}

// Consulta con contadores automáticos de ventas (según oportunidades)
$stmt = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM oportunidades o WHERE o.nombre_cliente = CONCAT(c.nombre, ' ', c.apellido) AND o.fase_venta = 'Cerrada ganada') AS ventas_ganadas,
    (SELECT COUNT(*) FROM oportunidades o WHERE o.nombre_cliente = CONCAT(c.nombre, ' ', c.apellido) AND o.fase_venta = 'Cerrada perdida') AS ventas_perdidas
    FROM clientes c ORDER BY c.id DESC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>CRM | Clientes</title>
<style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #EEEEEE;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: #1e293b;
        }


        /* =====================================
           SIDEBAR
        ====================================== */
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

    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: none;

    transition: width 0.25s ease;
}

        .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 10px;
    margin-bottom: 35px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 35px;

    flex-shrink: 0;
}

.sidebar-header .brand {
    margin-bottom: 0;
}


/* SIDEBAR COLAPSADO */
.sidebar.collapsed .sidebar-header {
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    gap: 12px;
}

.sidebar.collapsed .brand {
    margin-bottom: 0;
}

.sidebar.collapsed .sidebar-toggle {
    margin-left: 0;
}

        .sidebar-toggle {
    margin-left: auto;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 20px;
}

.sidebar-toggle:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.sidebar.collapsed .brand {
    padding: 0;
}

.sidebar.collapsed .sidebar-toggle {
    margin-left: 0;
}

        .brand-logo {
            width: 40px;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: #2563eb;
            color: white;

            font-size: 13px;
            font-weight: 700;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
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
    padding-top: 20px;
}

.user-dropdown {
    border-top: 1px solid #e5e7eb;
    padding-top: 10px;
}

.user-button {
    width: 100%;

    display: flex;
    align-items: center;

    gap: 10px;

    padding: 10px;

    border: none;
    border-radius: 8px;

    background: transparent;
    color: #1e293b;

    text-align: left;

    cursor: pointer;
}

.user-button:hover {
    background: #f1f5f9;
}

.user-avatar {
    width: 36px;
    height: 36px;

    min-width: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #e2e8f0;
    color: #475569;

    font-size: 13px;
    font-weight: 600;
}

.user-info {
    min-width: 0;
    flex: 1;
}

.user-name {
    font-size: 13px;
    font-weight: 600;
}

.user-role {
    font-size: 11px;
    color: #94a3b8;
}

.user-chevron {
    color: #94a3b8;
    font-size: 13px;
}

.user-menu {
    width: calc(100% - 32px);
    margin: 0 16px !important;

    border: 1px solid #e5e7eb;
    border-radius: 8px;

    box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
}

.user-menu .dropdown-item {
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 500;
}

/* SIDEBAR COLAPSADO */
.sidebar.collapsed {
    width: 70px;
}

/* Ocultar textos */
.sidebar.collapsed .brand-name,
.sidebar.collapsed .menu-title,
.sidebar.collapsed .menu-item span,
.sidebar.collapsed .user-info {
    display: none;
}

/* Centrar elementos */
.sidebar.collapsed .brand {
    justify-content: center;
    padding: 0;
}

.sidebar.collapsed .menu-item {
    justify-content: center;
}

.sidebar.collapsed .user {
    justify-content: center;
}

/* CONTENIDO */
.main {
    margin-left: 250px;
    min-height: 100vh;
    padding: 35px;
    transition: margin-left 0.25s ease;
}

.main.sidebar-collapsed {
    margin-left: 70px;
}

        /* =====================================
           CONTENIDO
        ====================================== */

        .main {
            margin-left: 250px;
            min-height: 100vh;
            padding: 35px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 25px;
        }

        .page-title {
            margin: 0;

            font-size: 24px;
            font-weight: 700;
        }

        .page-subtitle {
            margin-top: 5px;

            color: #64748b;
            font-size: 14px;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            text-decoration: none;
            transition: .2s;
            margin-bottom: 10px;
            border-radius: 8px;

            padding: 10px 16px;

            font-size: 14px;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }


        /* =====================================
           BOTÓN NUEVO CLIENTE
        ====================================== */

        .btn-new {
            background: #2563eb;
            border: none;

            border-radius: 8px;

            padding: 10px 16px;

            font-size: 14px;
            font-weight: 600;
        }


        /* =====================================
           TABLA
        ====================================== */

        .table-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow-x: auto;
        }
        .table {
            margin: 0;
        }

        .table thead th {
            background: #f8fafc;

            padding: 14px 16px;

            color: #64748b;

            font-size: 12px;
            font-weight: 600;

            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody td {
            padding: 15px 16px;

            vertical-align: middle;

            font-size: 14px;

            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .client-name {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;

            cursor: pointer;
        }

        .client-name:hover {
            text-decoration: underline;
        }


        /* =====================================
           BOTONES DE ACCIONES
        ====================================== */

        .action-btn {
            width: 32px;
            height: 32px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #e2e8f0;
            border-radius: 7px;

            background: white;
            color: #64748b;

            margin-left: 4px;
        }

        .action-btn:hover {
            background: #f8fafc;
        }

        .action-delete:hover {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
        }


        /* =====================================
           OFFCANVAS CLIENTE
        ====================================== */

        .offcanvas {
            width: 480px !important;
            border-left: 1px solid #e5e7eb;
        }

        .offcanvas-header {
            padding: 22px 25px;

            border-bottom: 1px solid #e5e7eb;
        }

        .offcanvas-title {
            font-size: 19px;
            font-weight: 700;
        }

        .offcanvas-body {
            padding: 0;
        }


        /* =====================================
           SECCIONES DEL CLIENTE
        ====================================== */

        .client-section {
            padding: 24px 25px;

            border-bottom: 1px solid #e5e7eb;
        }

        .section-title {
            margin-bottom: 18px;

            color: #64748b;

            font-size: 11px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: .5px;
        }


        /* DATOS */

        .data-row {
            display: flex;

            margin-bottom: 13px;

            font-size: 14px;
        }

        .data-label {
            width: 115px;

            color: #64748b;
            font-weight: 500;
        }

        .data-value {
            color: #1e293b;
            font-weight: 600;
        }


        /* =====================================
           COMENTARIO
        ====================================== */

        .comment-box {
            width: 100%;
            min-height: 100px;

            padding: 12px;

            resize: vertical;

            border: 1px solid #e2e8f0;
            border-radius: 8px;

            background: #f8fafc;

            font-size: 14px;
        }

        .comment-box:focus {
            outline: none;

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, .1);
        }

        .btn-save {
            margin-top: 10px;

            background: #2563eb;
            border: none;

            border-radius: 7px;

            padding: 8px 15px;

            font-size: 13px;
            font-weight: 600;
        }


        /* =====================================
           HISTORIAL
        ====================================== */

        .history {
            position: relative;
        }

        .history-item {
            position: relative;

            padding-left: 25px;
            padding-bottom: 22px;
        }

        .history-item::before {
            content: "";

            position: absolute;

            left: 5px;
            top: 7px;

            width: 9px;
            height: 9px;

            border-radius: 50%;

            background: #2563eb;
        }

        .history-item::after {
            content: "";

            position: absolute;

            left: 9px;
            top: 17px;

            width: 1px;
            height: calc(100% - 5px);

            background: #e2e8f0;
        }

        .history-item:last-child::after {
            display: none;
        }

        .history-user {
            font-size: 13px;
            font-weight: 700;
        }

        .history-description {
            margin-top: 3px;

            color: #475569;

            font-size: 13px;
        }

        .history-date {
            margin-top: 3px;

            color: #94a3b8;

            font-size: 11px;
        }


        /* =====================================
           RESPONSIVE
        ====================================== */
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
        width: 70%;
        height: 100vh;

        position: fixed;

        overflow-y: auto;
        overflow-x: hidden;
    }

    .sidebar .brand-name,
    .sidebar .menu-title,
    .sidebar .menu-item span,
    .sidebar .user-info {
        display: block;
    }

    .sidebar-header {
        width: 100%;
    }

    .sidebar-header .brand {
        margin-bottom: 0;
    }

    .sidebar .brand {
        justify-content: flex-start;
        padding: 0 10px;
    }

    .sidebar .menu-item {
        justify-content: flex-start;
    }

    .sidebar .user {
        justify-content: flex-start;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .page-header {
                flex-direction: column;
                gap: 15px;
            }

    .offcanvas {
        width: 100% !important;
    }
}


    </style>

</head>
<body>
<!-- =====================================
     SIDEBAR
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
            <a href="dashboard.php" class="menu-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard"><i class="bi bi-grid"></i><span>Dashboard</span></a>
            <a href="clientes.php" class="menu-item active" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Clientes"><i class="bi bi-people"></i><span>Clientes</span></a>
            <a href="oportunidades.php" class="menu-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Oportunidades"><i class="bi bi-briefcase"></i><span>Oportunidades</span></a>
            <a href="calendario.php" class="menu-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario</span></a>
        </nav>
        <div class="menu-separator"></div>
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <div class="menu-title">Sistema</div>
        <nav class="menu">
            <a href="reportes.php" class="menu-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Reportes"><i class="bi bi-bar-chart"></i><span>Reportes</span></a>
            <a href="/usuarios.php" class="menu-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Configuración"><i class="bi bi-gear"></i><span>Configuración</span></a>
        <?php endif; ?>
        </nav>
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
     CONTENIDO CLIENTES
====================================== -->
    <main class="main">
        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Clientes</h1>
                <div class="page-subtitle">Gestiona y consulta la información de tus clientes.</div>
            </div>
            <a href="crear_cliente.php" class="btn btn-primary btn-new"><i class="bi bi-plus-lg me-1"></i> Nuevo cliente</a>
        </div>
        <?php echo $mensaje; ?>
        <!-- TABLA -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Asignado a</th>
                        <th>Ventas Ganadas</th>
                        <th>Ventas Perdidas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td>
                            <button type="button" class="btn client-name" data-bs-toggle="offcanvas" href="#vista<?php echo $cliente['id']; ?>">
                                <?php echo $cliente['nombre']; ?>
                            </button>
                        </td>
                        <td><?php echo $cliente['apellido']; ?></td>
                        <td><?php echo $cliente['telefono']; ?></td>
                        <td><?php echo $cliente['tipo']; ?></td>
                        <td><?php echo $cliente['asignado']; ?></td>
                        <td><span class="badge bg-success"><?php echo $cliente['ventas_ganadas']; ?></span></td>
                        <td><span class="badge bg-danger"><?php echo $cliente['ventas_perdidas']; ?></span></td>
                        <td>
                            <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
                            <a href="eliminar_cliente.php?id=<?php echo $cliente['id']; ?>" class="action-btn" onclick="return confirm('¿Seguro?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <!-- =====================================
                         PANEL LATERAL DEL CLIENTE
                    ====================================== -->
                    <div class="offcanvas offcanvas-end" id="vista<?php echo $cliente['id']; ?>" tabindex="-1">
                        <!-- CABECERA -->
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title">
                               <i class="bi bi-person-circle"></i>  <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="offcanvas-body">
                            <!-- =================================
                                 DATOS DEL CLIENTE
                            ================================== -->
                            <section class="client-section">
                                <div class="section-title">
                                    Información del cliente
                                </div>
                                <div class="data-row">
                                    <div class="data-label">
                                        Nombre
                                    </div>
                                    <div class="data-value">
                                        <?php echo $cliente['nombre']; ?>
                                    </div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">
                                        Apellido
                                    </div>
                                    <div class="data-value">
                                        <?php echo $cliente['apellido']; ?>
                                    </div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">
                                        Teléfono
                                    </div>
                                    <div class="data-value">
                                        <?php echo $cliente['telefono']; ?>
                                    </div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">
                                        Asignado a
                                    </div>
                                    <div class="data-value">
                                        <?php echo $cliente['asignado']; ?>
                                    </div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Ventas Ganadas</div>
                                    <div class="data-value text-success"><?php echo $cliente['ventas_ganadas']; ?></div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Ventas Perdidas</div>
                                    <div class="data-value text-danger"><?php echo $cliente['ventas_perdidas']; ?></div>
                                </div>
                            </section>
                            <!-- =================================
                                 COMENTARIOS
                            ================================== -->
                            <section class="client-section">
                                <div class="section-title">
                                    Comentarios
                                </div>
                                
                                <!-- Lista de comentarios ya existentes -->
                                <div style="max-height: 150px; overflow-y: auto; margin-bottom: 15px;">
                                    <?php
                                    $stmtCom = $pdo->prepare("SELECT * FROM comentarios_clientes WHERE cliente_id = ? ORDER BY fecha DESC");
                                    $stmtCom->execute([$cliente['id']]);
                                    $comentarios = $stmtCom->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php if ($comentarios): ?>
                                        <?php foreach ($comentarios as $c): ?>
                                            <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px; margin-bottom: 8px;">
                                                <div style="font-size: 11px; font-weight: 700; color: #2563eb;">
                                                    <?php echo $c['usuario']; ?> <span style="color: #94a3b8; font-weight: 400;"><?php echo $c['fecha']; ?></span>
                                                </div>
                                                <div style="font-size: 13px; color: #1e293b; margin-top: 2px;">
                                                    <?php echo $c['comentario']; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small" style="font-size: 12px;">Sin comentarios aún.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Formulario para agregar comentario -->
                                <form action="agregar_comentario.php" method="POST">
                                    <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                                    <textarea name="comentario" class="comment-box" placeholder="Escribe un comentario..." required></textarea>
                                    <button type="submit" class="btn btn-save text-white">
                                        <i class="bi bi-send me-1"></i> Agregar comentario
                                    </button>
                                </form>
                            </section>
                            <!-- =================================
                                 HISTORIAL
                            ================================== -->
                            <section class="client-section">
                                <div class="section-title">
                                    Historial de cambios
                                </div>
                                <?php
                                        $stmtHist = $pdo->prepare("SELECT * FROM historial_clientes WHERE cliente_id = ? ORDER BY fecha DESC");
                                        $stmtHist->execute([$cliente['id']]);
                                        $historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
                                        if ($historial): ?>
                                            <?php foreach ($historial as $h): ?>
                                <div class="history">
                                    <div class="history-item">
                                        <div class="history-user">
                                            <?php echo $h['usuario']; ?>
                                        </div>
                                        <div class="history-description">
                                            <?php echo $h['accion']; ?>
                                        </div>
                                        <div class="history-date">
                                            <?php echo $h['fecha']; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted small">Sin actualizaciones.</p>
                                        <?php endif; ?>
                                </div>
                            </section>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    </main>
    
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