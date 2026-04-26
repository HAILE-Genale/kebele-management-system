<?php
// c:\xampp\htdocs\kebele-management-system\scratch\update_db.php
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if columns exist first to avoid errors
    $check = $conn->query("SHOW COLUMNS FROM persons LIKE 'educational_level'");
    if ($check->rowCount() == 0) {
        $conn->exec("ALTER TABLE persons ADD COLUMN educational_level VARCHAR(100) AFTER marital_status");
        echo "Added educational_level column.\n";
    } else {
        echo "educational_level column already exists.\n";
    }

    $check = $conn->query("SHOW COLUMNS FROM persons LIKE 'occupational_level'");
    if ($check->rowCount() == 0) {
        $conn->exec("ALTER TABLE persons ADD COLUMN occupational_level VARCHAR(100) AFTER educational_level");
        echo "Added occupational_level column.\n";
    } else {
        echo "occupational_level column already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
