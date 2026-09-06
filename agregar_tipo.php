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
            // Redirige de vuelta al formulario, pero con la variable para mostrar el mensaje de éxito
            header('Location: crear_cliente.php?tipo_agregado=1');
            exit();
        } catch (PDOException $e) {
            header('Location: crear_cliente.php?tipo_error=1');
            exit();
        }
    }
}
?>