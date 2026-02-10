<?php
// db_connect.php - Simple database connection
function getDB() {
    try {
        // Try SQLite3 first
        if (class_exists('SQLite3')) {
            $db = new SQLite3('database/chemicals.db');
            return $db;
        }
        
        // Try PDO
        if (extension_loaded('pdo_sqlite')) {
            $db = new PDO('sqlite:database/chemicals.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        }
        
        // If neither works
        die("SQLite extension not available. Please install php-sqlite.");
        
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>