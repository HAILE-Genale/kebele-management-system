<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>Updating Database Schema</h2>";
    
    // Check and add educational_level
    $check = $conn->query("SHOW COLUMNS FROM persons LIKE 'educational_level'");
    if ($check->rowCount() == 0) {
        $conn->exec("ALTER TABLE persons ADD COLUMN educational_level VARCHAR(100) AFTER marital_status");
        echo "<p style='color:green;'>✅ Added 'educational_level' column.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'educational_level' column already exists.</p>";
    }

    // Check and add occupational_level
    $check = $conn->query("SHOW COLUMNS FROM persons LIKE 'occupational_level'");
    if ($check->rowCount() == 0) {
        $conn->exec("ALTER TABLE persons ADD COLUMN occupational_level VARCHAR(100) AFTER educational_level");
        echo "<p style='color:green;'>✅ Added 'occupational_level' column.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'occupational_level' column already exists.</p>";
    }

    echo "<p><strong>Update complete!</strong> You can now go back to <a href='analytics.php'>Analytics</a>.</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error updating database: " . $e->getMessage() . "</p>";
}
?>
