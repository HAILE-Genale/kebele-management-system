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

    <?php include 'includes/sidebar.php'; ?>

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
                    </div>
                </form>
            </div>

            <div class="card-table" style="margin-top: 30px;">
                <h3><i class="fas fa-list"></i> Registered Birth Certificates</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Cert No.</th>
                                <th>Child's Name</th>
                                <th>Sex</th>
                                <th>Date of Birth</th>
                                <th>Reg. Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $births = $conn->query("
                                SELECT b.id, b.certificate_number, b.registered_date, 
                                       p.first_name, p.father_name, p.grandfather_name, p.sex, p.date_of_birth
                                FROM birth_certificates b
                                JOIN persons p ON b.person_id = p.id
                                ORDER BY b.created_at DESC
                            ");
                            if($births->rowCount() > 0):
                                while($row = $births->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['certificate_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['father_name'] . ' ' . $row['grandfather_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sex']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($row['registered_date']); ?></td>
                                <td>
                                    <a href="print_birth.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" target="_blank" style="padding: 5px 10px; font-size: 0.85em; display: inline-block; width: auto;"><i class="fas fa-print"></i> Print</a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No birth certificates registered yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
