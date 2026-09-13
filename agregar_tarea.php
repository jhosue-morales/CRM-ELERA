<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oportunidad_id = $_POST['oportunidad_id'];
    $tarea = trim($_POST['tarea']);
    $prioridad = $_POST['prioridad'];
    $fecha_realizacion = $_POST['fecha_realizacion'];
    
    if (!empty($tarea)) {
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        
        $stmt = $pdo->prepare("INSERT INTO tareas_oportunidades (oportunidad_id, usuario, tarea, prioridad, fecha_realizacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$oportunidad_id, $usuario, $tarea, $prioridad, $fecha_realizacion]);
        
        // Redirigir con el modal abierto (usamos un parámetro para que se abra solo)
        header("Location: oportunidades.php?abierto=$oportunidad_id");
        exit();
    }
}
?>