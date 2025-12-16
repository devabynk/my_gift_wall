<?php
/**
 * Magical Moments - Configuration Example
 * 
 * Copy this file to config.php and update with your credentials.
 * NEVER commit config.php to version control!
 */

// ===========================================
// ENVIRONMENT SETTINGS
// ===========================================
define('APP_ENV', 'development'); // 'development' or 'production'
define('APP_DEBUG', true); // Set to false in production
define('APP_URL', 'http://localhost/christmassapp');

// ===========================================
// DATABASE CONFIGURATION
// ===========================================
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

// ===========================================
// SESSION CONFIGURATION
// ===========================================
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', APP_ENV === 'production' ? 1 : 0);
ini_set('session.use_strict_mode', 1);

// ===========================================
// ERROR HANDLING
// ===========================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// ===========================================
// DATABASE CONNECTION
// ===========================================
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        error_log("Database connection failed: " . $e->getMessage());
        die("Bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
    }
}
