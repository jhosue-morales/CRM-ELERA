<?php
// auth.php - Verifica la sesión y el rol del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificar que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// 2. Definir qué roles pueden ver qué páginas
// (Esta variable se define en cada página antes de incluir auth.php)
if (isset($roles_permitidos)) {
    $rol_actual = $_SESSION['usuario_rol'] ?? 'vendedor';
    
    if (!in_array($rol_actual, $roles_permitidos)) {
        // Si no tiene permiso, lo mandamos al dashboard
        header('Location: clientes.php');
        exit();
    }
}
?>