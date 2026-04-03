<?php
$host = 'localhost';
$db   = 'vexto_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=300",
    PDO::ATTR_TIMEOUT            => 30,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("SET NAMES utf8mb4");
     try {
         $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
         if ($stmt && $stmt->fetch()) {
             $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'imagen_url'");
             if (!$stmt || !$stmt->fetch()) {
                 $pdo->exec("ALTER TABLE properties ADD COLUMN imagen_url VARCHAR(255) DEFAULT NULL");
             }
         }
     } catch (Exception $e) {
         // Ignorar si la tabla no existe aún o si no se puede alterar
     }
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}
?>
