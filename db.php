<?php
// =========================================================
// db.php - Database Connection Configuration (FINAL VERSION)
// =========================================================

// ENABLE ALL PHP AND DATABASE ERROR REPORTING FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Credentials (Ensure these are 100% correct)
$host = 'localhost';
$db   = 'rsoa_rsoa00112_3'; 
$user = 'rsoa_rsoa00112_3';
$pass = '654321#';          
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If connection fails, output a clear error message
     die("
     <div style='border: 1px solid red; padding: 20px; background-color: #ffe6e6; color: #cc0000; font-family: sans-serif; text-align: center;'>
        <h2>🛑 DATABASE CONNECTION FAILED!</h2>
        <p><strong>Database Name:</strong> {$db}</p>
        <p><strong>User:</strong> {$user}</p>
        <p><strong>Error Details:</strong></p>
        <pre>{$e->getMessage()}</pre>
        <p>Fix the credentials in <code>db.php</code> or ensure the database/tables are created with <code>db_setup_v3.sql</code>.</p>
     </div>
     ");
}
?>
