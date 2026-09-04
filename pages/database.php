<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'crm-bd';     // El nombre de tu base de datos
$user = 'root';             // Usuario de XAMPP
$password = '';            // Contraseña de XAMPP (vacía por defecto)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>