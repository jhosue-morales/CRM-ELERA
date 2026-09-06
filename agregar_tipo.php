<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_tipo = trim($_POST['nuevo_tipo']);
    if (!empty($nombre_tipo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tipos_clientes (nombre) VALUES (?)");
            $stmt->execute([$nombre_tipo]);
            echo "<script>alert('Tipo agregado correctamente.'); window.location.href='crear_cliente.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Error: Ese tipo ya existe o el nombre está vacío.'); window.location.href='crear_cliente.php';</script>";
            exit();
        }
    }
}
?>