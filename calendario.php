<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

$usuario_actual = $_SESSION['usuario_nombre'] ?? '';

// Consulta de tareas con información de la oportunidad
$stmt = $pdo->query("SELECT t.*, o.nombre_oportunidad 
    FROM tareas_oportunidades t 
    LEFT JOIN oportunidades o ON t.oportunidad_id = o.id 
    ORDER BY t.fecha_realizacion ASC");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tareas_json = json_encode($tareas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <title>Calendario de Tareas - CRM</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEEEEE; font-family: "Inter", "Segoe UI", sans-serif; color: #1e293b; }
        
        /* SIDEBAR */
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

        /* CONTENIDO */
        .main { margin-left: 250px; min-height: 100vh; padding: 35px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }
        .page-subtitle { margin-top: 5px; color: #64748b; font-size: 14px; }

        /* FILTROS */
        .filtros-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filtro-btn { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; font-size: 14px; font-weight: 500; background: white; color: #64748b; cursor: pointer; transition: all 0.2s; }
        .filtro-btn:hover { background: #f8fafc; }
        .filtro-btn.active { background: #2563eb; color: white; border-color: #2563eb; }

        /* CALENDARIO */
        #calendar { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
        .fc-event { cursor: pointer; border: none !important; padding: 2px 5px; }
        .fc-event-title { font-weight: 500; }
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
        <a href="calendario.php" class="menu-item active"><i class="bi bi-calendar-event"></i><span>Calendario</span></a>
        <a href="#" class="menu-item"><i class="bi bi-cart3"></i><span>Ventas</span></a>
    </nav>
    <div class="menu-separator"></div>
    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="menu-title">Sistema</div>
    <nav class="menu">
        <a href="#" class="menu-item"><i class="bi bi-bar-chart"></i><span>Reportes</span></a>
        <a href="/usuarios.php" class="menu-item"><i class="bi bi-gear"></i><span>Configuración</span></a>
    </nav>
    <?php endif; ?>
    <div class="sidebar-bottom">
        <div class="user">
            <div class="user-avatar"><a href="logout.php" class="text-decoration-none text-dark">CS</a></div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['usuario_rol'] ?? 'Admin'); ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- CONTENIDO -->
<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Calendario de Tareas</h1>
            <div class="page-subtitle">Visualiza las tareas de seguimiento programadas.</div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-card">
        <span style="font-weight: 600; color: #64748b; font-size: 14px;">Mostrar:</span>
        <button class="filtro-btn active" onclick="filtrarTareas('todas', this)">Todas las tareas</button>
        <button class="filtro-btn" onclick="filtrarTareas('mias', this)">Solo mis tareas</button>
    </div>

    <!-- CALENDARIO -->
    <div id="calendar"></div>
</main>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    // Datos de las tareas desde PHP
    const tareas = <?php echo $tareas_json; ?>;
    const usuarioActual = "<?php echo $usuario_actual; ?>";

    // Colores por prioridad
    const colores = {
        'Alta': '#dc2626',
        'Media': '#f59e0b',
        'Baja': '#0ea5e9'
    };

    // Convertir tareas a eventos del calendario
    function formatearEventos(listaTareas) {
        return listaTareas.map(t => ({
            id: t.id,
            title: t.tarea + ' | ' + (t.nombre_oportunidad || 'Sin oportunidad'),
            start: t.fecha_realizacion,
            backgroundColor: colores[t.prioridad] || '#64748b',
            borderColor: colores[t.prioridad] || '#64748b',
            extendedProps: {
                prioridad: t.prioridad,
                asignado_a: t.asignado_a,
                oportunidad: t.nombre_oportunidad,
                usuario: t.usuario
            }
        }));
    }

    // Inicializar el calendario
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                list: 'Lista'
            },
            events: formatearEventos(tareas),
            eventClick: function(info) {
                const props = info.event.extendedProps;
                alert(
                    "📋 TAREA: " + info.event.title + "\n\n" +
                    "⚡ Prioridad: " + props.prioridad + "\n" +
                    "👤 Asignada a: " + props.asignado_a + "\n" +
                    "📁 Oportunidad: " + props.oportunidad + "\n" +
                    "✍️ Creada por: " + props.usuario
                );
            }
        });
        calendar.render();

        // Exponer el calendario para los filtros
        window.calendarioGlobal = calendar;
    });

    // Función para filtrar
    function filtrarTareas(tipo, boton) {
        // Cambiar el botón activo
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
        boton.classList.add('active');

        // Filtrar las tareas
        let tareasFiltradas = tareas;
        if (tipo === 'mias') {
            tareasFiltradas = tareas.filter(t => t.asignado_a === usuarioActual);
        }

        // Actualizar el calendario
        const calendar = window.calendarioGlobal;
        calendar.removeAllEvents();
        calendar.addEventSource(formatearEventos(tareasFiltradas));
    }
</script>
</body>
</html>