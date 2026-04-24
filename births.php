<?php
// births.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect Person Data
    $first_name = $_POST['first_name'];
    $father_name = $_POST['father_name'];
    $grandfather_name = $_POST['grandfather_name'];
    $sex = $_POST['sex'];
    $date_of_birth = $_POST['date_of_birth'];
    $place_of_birth = $_POST['place_of_birth'];
    $nationality = $_POST['nationality'];

    // Collect Birth Details Data
    $mother_name = $_POST['mother_name'];
    $mother_nationality = $_POST['mother_nationality'];
    $father_nationality = $_POST['father_nationality'];
    
    // Auto-generate certificate number
    $cert_num = "BR-" . rand(1000, 9999) . "-" . date('Y');
    $registrar_id = $_SESSION['user_id'];
    $registered_date = date('Y-m-d');

    try {
        $conn->beginTransaction();

        // 1. Insert into Persons
        $stmt_p = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_p->execute([$first_name, $father_name, $grandfather_name, $sex, $date_of_birth, $place_of_birth, $nationality]);
        
        $person_id = $conn->lastInsertId();

        // 2. Insert into Birth Certificates
        $stmt_b = $conn->prepare("INSERT INTO birth_certificates (certificate_number, person_id, mother_name, mother_nationality, father_nationality, registrar_id, registered_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_b->execute([$cert_num, $person_id, $mother_name, $mother_nationality, $father_nationality, $registrar_id, $registered_date]);
        
        $cert_id = $conn->lastInsertId();
        
        $conn->commit();
        
        // Redirect directly to the certificate print page!
        header("Location: print_birth.php?id=" . $cert_id);
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to register: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Birth - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .section-title { grid-column: 1 / -1; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; color: var(--primary-color); }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 4px; margin-bottom: 15px;}
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>CIVIL REGISTRY</h3>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="persons.php"><i class="fas fa-users"></i> Citizens</a></li>
            <li><a href="births.php" class="active"><i class="fas fa-baby"></i> Register Birth</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <h2>Register New Birth & Generate Certificate</h2>
        </div>

        <div class="content-wrapper">
            <div class="card-table">
                <?php if($error): echo "<div class='error'>$error</div>"; endif; ?>
                
                <form action="" method="POST">
                    <div class="form-grid">
                        <div class="section-title">Child Details (Person Record)</div>
                        
                        <div class="form-group">
                            <label>Child's First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Child's Father's Name</label>
                            <input type="text" name="father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Child's Grandfather's Name</label>
                            <input type="text" name="grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select name="sex" class="form-control" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="place_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="Ethiopian" required>
                        </div>

                        <div class="section-title">Birth Certificate Specific Details</div>
                        
                        <div class="form-group">
                            <label>Mother's Full Name</label>
                            <input type="text" name="mother_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Mother's Nationality</label>
                            <input type="text" name="mother_nationality" class="form-control" value="Ethiopian" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Nationality</label>
                            <input type="text" name="father_nationality" class="form-control" value="Ethiopian" required>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1; margin-top:20px;">
                            <button type="submit" class="btn btn-primary" style="font-size: 1.1em; padding: 15px;"><i class="fas fa-print"></i> Register & Print Template</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
