<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'database.php';



$mensaje = '';
if (isset($_GET['eliminado'])) {
    $mensaje = '<div class="alert alert-success">Oportunidad eliminada.</div>';
}

$stmt = $pdo->query("SELECT o.*, CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente_actual 
    FROM oportunidades o 
    LEFT JOIN clientes c ON o.nombre_cliente = CONCAT(c.nombre, ' ', c.apellido) 
    ORDER BY o.id DESC");
$oportunidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>Oportunidades - Mi CRM</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; color: #1e293b; }
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
        .main { margin-left: 250px; min-height: 100vh; padding: 35px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }
        .page-subtitle { margin-top: 5px; color: #64748b; font-size: 14px; }
        .btn-new { background: #2563eb; border: none; border-radius: 8px; padding: 10px 16px; font-size: 14px; font-weight: 600; }
        .table-container { background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow-x: auto; }
        .table { margin: 0; }
        .table thead th { background: #f8fafc; padding: 14px 16px; color: #64748b; font-size: 12px; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 15px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:last-child td { border-bottom: none; }
        
        /* Fases de venta coloreadas */
        .fase-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .fase-contactado { background: #e0f2fe; color: #0284c7; }
        .fase-analizando { background: #fef3c7; color: #d97706; }
        .fase-cotizacion { background: #dcfce7; color: #16a34a; }
        .fase-esperando { background: #f3e8ff; color: #9333ea; }
        .fase-negociacion { background: #ffedd5; color: #ea580c; }
        .fase-ganada { background: #22c55e; color: white; }
        .fase-perdida { background: #ef4444; color: white; }

        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 7px; background: white; color: #64748b; margin-left: 4px; }
        .action-btn:hover { background: #f8fafc; }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .brand { justify-content: center; padding: 0; }
            .brand-name, .menu-title, .menu-item span, .user-info { display: none; }
            .menu-item { justify-content: center; }
            .main { margin-left: 70px; padding: 20px; }
        }
                /* =====================================
           SECCIONES (COPIADAS DE CLIENTES)
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
        .comment-box {
            width: 100%;
            min-height: 80px;
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }
        .btn-save {
            background: #2563eb;
            border: none;
            border-radius: 7px;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-save:hover {
            background: #1d4ed8;
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
        <a href="oportunidades.php" class="menu-item active"><i class="bi bi-briefcase"></i><span>Oportunidades</span></a>
        <a href="#" class="menu-item"><i class="bi bi-file-earmark-text"></i><span>Cotizaciones</span></a>
        <a href="#" class="menu-item"><i class="bi bi-cart3"></i><span>Ventas</span></a>
    </nav>
    <div class="menu-separator"></div>
    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="menu-title">Sistema</div>
    <nav class="menu">
        
            <a href="#" class="menu-item">
                <i class="bi bi-bar-chart"></i>
                <span>Reportes</span>
            </a>
            <a href="/usuarios.php" class="menu-item">
                <i class="bi bi-gear"></i>
                <span>Configuración</span>
            </a>
    <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="user">
            <div class="user-avatar"><a href="logout.php" class="text-decoration-none text-dark">CS</a></div>
            <div class="user-info"><div class="user-name">Usuario</div><div class="user-role">Administrador</div></div>
        </div>
    </div>
</aside>

<!-- CONTENIDO -->
<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Oportunidades</h1>
            <div class="page-subtitle">Gestiona las oportunidades de venta de tus clientes.</div>
        </div>
        <a href="crear_oportunidad.php" class="btn btn-primary btn-new text-white text-decoration-none">
            <i class="bi bi-plus-lg me-1"></i> Nueva oportunidad
        </a>
    </div>
    <?php echo $mensaje; ?>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre Oportunidad</th>
                    <th>Asignado a</th>
                    <th>Nombre de Contacto</th>
                    <th>Fase de Venta</th>
                    <th>Probabilidad</th>
                    <th>N° Cotización</th>
                    <th>Fecha de Creación</th>
                    <th>Fecha Estimada de Cierre</th>
                    <th>Documento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($oportunidades as $op): ?>
<tr>
    <td style="font-weight: 600; color: #2563eb;"><?php echo $op['nombre_oportunidad']; ?></td>
    <td><?php echo $op['asignado_a']; ?></td>
    <td><?php echo $op['nombre_cliente_actual'] ?? $op['nombre_cliente']; ?></td>
    <td>
        <?php
        $fase = strtolower($op['fase_venta']);
        $clase_fase = 'fase-contactado';
        if (strpos($fase, 'analizando') !== false) $clase_fase = 'fase-analizando';
        if (strpos($fase, 'cotizacion') !== false) $clase_fase = 'fase-cotizacion';
        if (strpos($fase, 'esperando') !== false) $clase_fase = 'fase-esperando';
        if (strpos($fase, 'negociacion') !== false) $clase_fase = 'fase-negociacion';
        if (strpos($fase, 'ganada') !== false) $clase_fase = 'fase-ganada';
        if (strpos($fase, 'perdida') !== false) $clase_fase = 'fase-perdida';
        ?>
        <span class="fase-badge <?php echo $clase_fase; ?>"><?php echo $op['fase_venta']; ?></span>
    </td>
    <td><?php echo $op['probabilidad'] . '%'; ?></td>
    <td><?php echo $op['numero_cotizacion']; ?></td>
    <td><?php echo $op['fecha_creacion']; ?></td>
    <td><?php echo $op['fecha_estimada_cierre']; ?></td>
    <td>
        <?php if (!empty($op['archivo_cotizacion'])): ?>
            <a href="<?php echo $op['archivo_cotizacion']; ?>" class="btn btn-sm btn-success" target="_blank">
                <i class="bi bi-download"></i> Descargar
            </a>
        <?php else: ?>
            <span class="text-muted">No hay archivo</span>
        <?php endif; ?>
    </td>
    <td>
        <button class="action-btn" data-bs-toggle="offcanvas" data-bs-target="#tareas<?php echo $op['id']; ?>">
            <i class="bi bi-list-task"></i>
        </button>
        <a href="editar_oportunidad.php?id=<?php echo $op['id']; ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
        <a href="eliminar_oportunidad.php?id=<?php echo $op['id']; ?>" class="action-btn" onclick="return confirm('¿Seguro?')"><i class="bi bi-trash"></i></a>
    </td>
</tr>

<!-- =====================================
     PANEL LATERAL DE TAREAS
====================================== -->
<div class="offcanvas offcanvas-end" id="tareas<?php echo $op['id']; ?>" tabindex="-1">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="bi bi-list-task"></i> Tareas: <?php echo $op['nombre_oportunidad']; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <!-- =================================
             LISTA DE TAREAS EXISTENTES
        ================================== -->
        <section class="client-section">
            <div class="section-title">Tareas de Seguimiento</div>
            <div style="max-height: 400px; overflow-y: auto; margin-bottom: 15px;">
                <?php
                $stmtTar = $pdo->prepare("SELECT * FROM tareas_oportunidades WHERE oportunidad_id = ? ORDER BY fecha_realizacion ASC");
                $stmtTar->execute([$op['id']]);
                $tareas = $stmtTar->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if ($tareas): ?>
                    <?php foreach ($tareas as $t): ?>
                        <?php
                        $color_prioridad = 'bg-secondary';
                        if ($t['prioridad'] == 'Alta') $color_prioridad = 'bg-danger';
                        if ($t['prioridad'] == 'Media') $color_prioridad = 'bg-warning text-dark';
                        if ($t['prioridad'] == 'Baja') $color_prioridad = 'bg-info text-dark';
                        ?>
                        <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <span class="badge <?php echo $color_prioridad; ?>"><?php echo $t['prioridad']; ?></span>
                                <span style="font-size: 11px; color: #94a3b8;"><?php echo $t['fecha_realizacion']; ?></span>
                            </div>
                            <div style="font-size: 13px; color: #1e293b; margin-bottom: 5px;">
                                <?php echo $t['tarea']; ?>
                            </div>
                            <div style="font-size: 11px; color: #94a3b8;">
                                Asignada por: <strong><?php echo $t['usuario']; ?></strong> el <?php echo $t['fecha_creacion']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small" style="font-size: 12px;">Sin tareas de seguimiento aún.</p>
                <?php endif; ?>
            </div>

            <!-- Formulario para agregar nueva tarea -->
            <form action="agregar_tarea.php" method="POST">
                <input type="hidden" name="oportunidad_id" value="<?php echo $op['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Tarea</label>
                    <textarea name="tarea" class="comment-box" placeholder="Describe la tarea a realizar..." required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select" required>
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de realización</label>
                        <input type="date" name="fecha_realizacion" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-save text-white w-100">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Tarea
                </button>
            </form>
        </section>
    </div>
</div>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_GET['abierto']) && $_GET['abierto'] > 0): ?>
        var miOffcanvas = document.getElementById('tareas<?php echo $_GET['abierto']; ?>');
        if (miOffcanvas) {
            var instancia = new bootstrap.Offcanvas(miOffcanvas);
            instancia.show();
        }
    <?php endif; ?>
});
</script>
</body>
</html>