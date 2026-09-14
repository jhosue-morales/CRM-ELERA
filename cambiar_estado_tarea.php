<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = $_GET['id'];
    $estado = $_GET['estado'];
    
    // Validar que solo se puedan usar esos dos estados
    if (in_array($estado, ['Pendiente', 'Realizada'])) {
        $stmt = $pdo->prepare("UPDATE tareas_oportunidades SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
    }
}
header("Location: oportunidades.php");
exit();
?>