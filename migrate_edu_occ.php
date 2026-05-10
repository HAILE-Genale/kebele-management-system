<?php
// One-time migration: adds educational_level & occupational_status to persons table.
// Open this page once in the browser, then DELETE it.
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

$queries = [
    "ALTER TABLE persons ADD COLUMN IF NOT EXISTS educational_level 
        ENUM('No Formal Education','Primary (1-8)','Secondary (9-12)','Certificate/Diploma',
             'Bachelor\\'s Degree','Master\\'s Degree','PhD/Doctorate') 
        DEFAULT 'No Formal Education'",
    "ALTER TABLE persons ADD COLUMN IF NOT EXISTS occupational_status 
        ENUM('Employed','Self-Employed','Unemployed','Student','Retired','Farmer','Housewife','Other') 
        DEFAULT 'Unemployed'",
];

echo "<h2>Migration: Educational Level & Occupational Status</h2><ul>";
foreach ($queries as $sql) {
    try {
        $conn->exec($sql);
        echo "<li style='color:green'>✔ OK: " . htmlspecialchars(trim(substr($sql, 0, 80))) . "...</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>✘ Error: " . htmlspecialchars($e->getMessage()) . "</li>";
    }
}
echo "</ul><p><strong>Done! Delete this file (migrate_edu_occ.php) when finished.</strong></p>";
echo "<p><a href='persons.php'>Go to Citizens Registry →</a></p>";
