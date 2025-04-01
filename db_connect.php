<?php
require_once 'db_config.php';

/**
 * Establishes a database connection using PDO
 * @return PDO - Database connection object
 */
function getDbConnection() {
    global $dsn, $user, $password;
    
    try {
        $pdo = new PDO($dsn, $user, $password);
        // Set PDO to throw exceptions on error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error and return a user-friendly message
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}
?> 