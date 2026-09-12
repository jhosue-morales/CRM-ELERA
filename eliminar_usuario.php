<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
if ($_SESSION['usuario_rol'] !== 'admin') { header('Location: dashboard.php?error=sin_permiso'); exit(); }
require_once 'database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Evitar que el usuario se elimine a sí mismo
    if ($id != $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }
}
header("Location: usuarios.php");
exit();
?>