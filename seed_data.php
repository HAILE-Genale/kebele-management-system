<?php
// c:\xampp\htdocs\kebele-management-system\scratch\seed_data.php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Disable foreign key checks for seeding
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    $first_names_m = ['Abebe', 'Bekele', 'Chala', 'Dawit', 'Eskinder', 'Fasil', 'Girum', 'Haile', 'Ismael', 'Jember', 'Kassa', 'Lulseged', 'Mulugeta', 'Negash', 'Omer', 'Paulos', 'Qasim', 'Robel', 'Samuel', 'Tadesse', 'Umer', 'Vicky', 'Wondimu', 'Yonas', 'Zewdu'];
    $first_names_f = ['Almaz', 'Birtukan', 'Chaltu', 'Desta', 'Emebet', 'Fikirte', 'Genet', 'Hana', 'Ipsitu', 'Jalene', 'Kalkidan', 'Liyu', 'Marta', 'Nigist', 'Obse', 'Pikire', 'Rahel', 'Selam', 'Tigist', 'Urge', 'Worknesh', 'Yemisrach', 'Zahara'];
    $last_names = ['Kebede', 'Bekele', 'Tadesse', 'Mulugeta', 'Girma', 'Haile', 'Tesfaye', 'Abraham', 'Mohammed', 'Ahmed', 'Zeleke', 'Alemu', 'Ayale', 'Desta', 'Fikre', 'Getachew', 'Habte', 'Ibrahim', 'Jafar', 'Kassa'];

    $sexes = ['Male', 'Female'];
    $maritals = ['Single', 'Married', 'Divorced', 'Widowed'];
    $edus = ['No Formal Education', 'Primary (1-8)', 'Secondary (9-12)', 'Certificate/Diploma', "Bachelor's Degree", "Master's Degree", 'PhD/Doctorate'];
    $occs = ['Employed', 'Self-Employed', 'Unemployed', 'Student', 'Retired', 'Farmer', 'Housewife', 'Other'];
    $places = ['Addis Ababa', 'Jimma', 'Adama', 'Bahir Dar', 'Hawassa', 'Mekelle', 'Dire Dawa', 'Gondar', 'Dessie', 'Haramaya'];

    echo "<h2>Seeding 200 People...</h2>";
    
    $stmt = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, marital_status, educational_level, occupational_status, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

    for ($i = 0; $i < 200; $i++) {
        $sex = $sexes[array_rand($sexes)];
        $fn = ($sex == 'Male') ? $first_names_m[array_rand($first_names_m)] : $first_names_f[array_rand($first_names_f)];
        $mn = $last_names[array_rand($last_names)];
        $gn = $last_names[array_rand($last_names)];
        
        $dob = date('Y-m-d', strtotime('-' . rand(0, 80) . ' years -' . rand(0, 365) . ' days'));
        $pob = $places[array_rand($places)];
        $marital = $maritals[array_rand($maritals)];
        $edu = $edus[array_rand($edus)];
        $occ = $occs[array_rand($occs)];
        $status = (rand(0, 10) > 8) ? 'Inactive' : 'Active'; // 20% inactive (deceased etc)

        $stmt->execute([$fn, $mn, $gn, $sex, $dob, $pob, 'Ethiopian', $marital, $edu, $occ, $status]);
    }

    echo "<p style='color:green;'>✅ Successfully registered 200 people with Ethiopian names and diverse data.</p>";
    
    // Seed some certificates to make analytics even better
    $person_ids = $conn->query("SELECT id FROM persons")->fetchAll(PDO::FETCH_COLUMN);
    
    // Births
    $stmt_birth = $conn->prepare("INSERT INTO birth_certificates (certificate_number, person_id, mother_name, registered_date) VALUES (?,?,?,?)");
    for ($i = 0; $i < 50; $i++) {
        $stmt_birth->execute(['B-' . rand(10000, 99999), $person_ids[array_rand($person_ids)], $first_names_f[array_rand($first_names_f)] . ' ' . $last_names[array_rand($last_names)], date('Y-m-d', strtotime('-' . rand(0, 365) . ' days'))]);
    }

    // Deaths
    $stmt_death = $conn->prepare("INSERT INTO death_certificates (certificate_number, person_id, date_of_death, registered_date) VALUES (?,?,?,?)");
    for ($i = 0; $i < 20; $i++) {
        $pid = $person_ids[array_rand($person_ids)];
        $stmt_death->execute(['D-' . rand(10000, 99999), $pid, date('Y-m-d'), date('Y-m-d')]);
        $conn->prepare("UPDATE persons SET status = 'Inactive' WHERE id = ?")->execute([$pid]);
    }

    echo "<p style='color:green;'>✅ Seeded 50 Births and 20 Deaths.</p>";
    echo "<p>Go to <a href='analytics.php'>Analytics Dashboard</a> to see the results!</p>";

    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error seeding data: " . $e->getMessage() . "</p>";
}
?>
