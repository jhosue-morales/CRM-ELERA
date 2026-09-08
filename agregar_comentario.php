<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $comentario = trim($_POST['comentario']);
    
    if (!empty($comentario)) {
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        
        // Guardar el comentario nuevo
        $stmt = $pdo->prepare("INSERT INTO comentarios_clientes (cliente_id, usuario, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $usuario, $comentario]);
        
        // Registrar en el historial de cambios
        $stmtHist = $pdo->prepare("INSERT INTO historial_clientes (cliente_id, usuario, accion) VALUES (?, ?, ?)");
        $stmtHist->execute([$cliente_id, $usuario, 'Comentario agregado']);
        
        // Redirigir de vuelta con el panel abierto
        header("Location: clientes.php?abierto=$cliente_id");
        exit();
    }
}
?>