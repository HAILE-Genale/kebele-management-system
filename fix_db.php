<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    echo "Attempting to fix database schema...<br>";
    
    // Check if status column exists
    $stmt = $conn->query("SHOW COLUMNS FROM persons LIKE 'status'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $conn->exec("ALTER TABLE persons ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER marital_status");
        echo "Successfully added 'status' column to 'persons' table.<br>";
    } else {
        echo "'status' column already exists.<br>";
    }

    echo "Database fix completed. You can delete this file now.";
} catch (Exception $e) {
    echo "Error fixing database: " . $e->getMessage();
}
?>
