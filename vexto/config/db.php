<?php
/**
 * Database Connection Configuration
 * 
 * This file handles all database connections using PDO
 * with proper error handling and security best practices.
 */

// Load environment variables or use defaults
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'vexto_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=300",
    PDO::ATTR_TIMEOUT => 30,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET NAMES utf8mb4");
    
    // Auto-migrate: Add missing columns if needed
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
        if ($stmt && $stmt->fetch()) {
            $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'imagen_url'");
            if (!$stmt || !$stmt->fetch()) {
                $pdo->exec("ALTER TABLE properties ADD COLUMN imagen_url VARCHAR(255) DEFAULT NULL");
            }
        }
    } catch (Exception $e) {
        // Silently ignore if table doesn't exist yet
    }
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
