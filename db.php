<?php
// db.php - Database Connection Setup
// IMPORTANT: Credentials are set here.

$host = 'localhost';
$db   = 'hjhdvzgw_smartbookr_db'; 
$user = 'hjhdvzgw_smartbookr'; 
$pass = '1701Qaz!'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Establish the PDO connection in the global $pdo variable
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If the connection fails, enable debug and output the error.
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);

     // Output the specific connection error and stop execution.
     die("<h1>FATAL CONNECTION ERROR: </h1><p>Database connection failed: " . $e->getMessage() . "</p>");
}

// ---------------------------------------------------------------------
// --- SCHEMA INITIALIZATION (REMOVED: MUST BE HANDLED SEPARATELY) ---
// Note: Automatic table creation logic was removed from this file.
// Please ensure the 'businesses', 'services', and 'appointments' tables
// exist in your database before running the application pages.
// ---------------------------------------------------------------------

// Ensure $pdo is available for inclusion files
global $pdo;

// Remove debug settings now that database connection is confirmed (or failed clearly)
ini_set('display_errors', 0);
error_reporting(0);

?>
