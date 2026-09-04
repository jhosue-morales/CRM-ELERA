<?php
// Obtenemos los datos directamente de las variables de entorno de Railway
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$dbname = getenv('MYSQL_DATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

try {
    // Creamos la conexión usando el puerto y el host de Railway
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Conexión exitosa"; // Descomenta esto si quieres probar si conecta
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>