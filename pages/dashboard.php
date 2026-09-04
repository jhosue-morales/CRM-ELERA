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
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard CRM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>📊 Dashboard</h1>
            <div>
                <span>👋 <?php echo $nombre; ?> (<?php echo $rol; ?>)</span>
                <a href="logout.php" class="btn-cerrar">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="contenido">
            <div class="card">
                <h3>✅ Login exitoso</h3>
                <p>Bienvenido al sistema CRM.</p>
            </div>
            
            <div class="card">
                <h3>📋 Próximos pasos</h3>
                <ul>
                    <li>Gestión de clientes</li>
                    <li>Gestión de productos</li>
                    <li>Sistema de ventas</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>