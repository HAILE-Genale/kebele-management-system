<?php
// deaths.php - Death Certificate Registration & CRUD
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// --- SELF-HEAL: Ensure 'status' column exists ---
try {
    $check = $conn->query("SHOW COLUMNS FROM persons LIKE 'status'")->fetch();
    if (!$check) {
        $conn->exec("ALTER TABLE persons ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER marital_status");
    }
} catch (Exception $e) { /* Ignore errors here */ }
// --- END SELF-HEAL ---

$error = '';
$success = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM death_certificates WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Death certificate deleted.";
}

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $father_name = trim($_POST['father_name']);
    $grandfather_name = trim($_POST['grandfather_name']);
    $sex = $_POST['sex'];
    $date_of_birth = $_POST['date_of_birth'];
    $place_of_birth = trim($_POST['place_of_birth']);
    $nationality = trim($_POST['nationality']);
    $title = trim($_POST['title']);
    $place_of_death = trim($_POST['place_of_death']);
    $date_of_death = $_POST['date_of_death'];

    $cert_num = "DT-" . rand(1000, 9999) . "-" . date('Y');
    $registrar_id = $_SESSION['user_id'];
    $registered_date = date('Y-m-d');

    try {
        $conn->beginTransaction();

        // Insert Person
        $stmt = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, status) VALUES (?,?,?,?,?,?,?,'Inactive')");
        $stmt->execute([$first_name, $father_name, $grandfather_name, $sex, $date_of_birth, $place_of_birth, $nationality]);
        $person_id = $conn->lastInsertId();

        // Insert Death Certificate
        $stmt = $conn->prepare("INSERT INTO death_certificates (certificate_number, person_id, title, place_of_death, date_of_death, registrar_id, registered_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$cert_num, $person_id, $title, $place_of_death, $date_of_death, $registrar_id, $registered_date]);
        $cert_id = $conn->lastInsertId();

        $conn->commit();
        header("Location: print_death.php?id=" . $cert_id);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
}

// Fetch all death certificates
$certs = $conn->query("SELECT d.*, p.first_name, p.father_name, p.grandfather_name FROM death_certificates d JOIN persons p ON d.person_id = p.id ORDER BY d.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deaths - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .section-title { grid-column: 1 / -1; margin: 15px 0 5px; border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; color: var(--primary-color); }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; width: auto; display: inline-block; }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .btn-info { background: #5bc0de; color: #fff; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h2>Death Certificate Registration</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="card-table" style="margin-bottom:25px;">
                <h3>Register Death Certificate</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="section-title">Deceased Person Details</div>
                        <div class="form-group">
                            <label>Deceased's First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Grandfather's Name</label>
                            <input type="text" name="grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Mr, Mrs, Dr">
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
                            <input type="text" name="place_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="Ethiopian">
                        </div>

                        <div class="section-title">Death Details</div>
                        <div class="form-group">
                            <label>Date of Death</label>
                            <input type="date" name="date_of_death" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Death</label>
                            <input type="text" name="place_of_death" class="form-control" required>
                        </div>

                        <div class="form-group" style="grid-column:1/-1; margin-top:15px;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Register & Print Certificate</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Existing Records -->
            <div class="card-table">
                <h3>Death Certificates (<?php echo count($certs); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Cert #</th>
                                <th>Deceased Name</th>
                                <th>Date of Death</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($certs) > 0): foreach($certs as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['certificate_number']); ?></td>
                                <td><?php echo htmlspecialchars($c['first_name'].' '.$c['father_name'].' '.$c['grandfather_name']); ?></td>
                                <td><?php echo $c['date_of_death']; ?></td>
                                <td><?php echo $c['registered_date']; ?></td>
                                <td>
                                    <a href="print_death.php?id=<?php echo $c['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-print"></i></a>
                                    <a href="deaths.php?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this certificate?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" style="text-align:center;">No death certificates found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
