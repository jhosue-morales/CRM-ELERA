<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM oportunidades WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: oportunidades.php");
exit();
?>