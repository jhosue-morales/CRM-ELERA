<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oportunidad_id = $_POST['oportunidad_id'];
    $tarea = trim($_POST['tarea']);
    $prioridad = $_POST['prioridad'];
    $fecha_realizacion = $_POST['fecha_realizacion'];
    
    // Lógica de asignación según el rol
    if ($_SESSION['usuario_rol'] === 'admin') {
        // Si es admin, puede elegir a quién asignar
        $asignado_a = $_POST['asignado_a'];
    } else {
        // Si es vendedor, se asigna a sí mismo automáticamente
        $asignado_a = $_SESSION['usuario_nombre'];
    }
    
    if (!empty($tarea)) {
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        
        $stmt = $pdo->prepare("INSERT INTO tareas_oportunidades (oportunidad_id, usuario, asignado_a, tarea, prioridad, fecha_realizacion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$oportunidad_id, $usuario, $asignado_a, $tarea, $prioridad, $fecha_realizacion]);
        
        header("Location: oportunidades.php?abierto=$oportunidad_id");
        exit();
    }
}
?>