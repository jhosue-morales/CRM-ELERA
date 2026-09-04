<?php
// Obtenemos los datos desde las variables de entorno de Railway
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$dbname = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

// Si por alguna razón las variables están vacías, usa los valores por defecto de Railway
if (!$host) $host = 'mysql.railway.internal';
if (!$port) $port = '3306';
if (!$dbname) $dbname = 'railway';
if (!$user) $user = 'root';

try {
    // Forzamos a PHP a conectarse por IP y puerto (NO por socket local)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Descomenta la siguiente línea si quieres probar si conectó
    // echo "Conexión exitosa";

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>