<?php
// divorces.php - Divorce Certificate Registration & CRUD
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM divorce_certificates WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Divorce certificate deleted.";
}

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Husband
    $h_first = trim($_POST['h_first_name']);
    $h_father = trim($_POST['h_father_name']);
    $h_grandfather = trim($_POST['h_grandfather_name']);
    $h_dob = $_POST['h_date_of_birth'];
    $h_pob = trim($_POST['h_place_of_birth']);
    $h_nationality = trim($_POST['h_nationality']);

    // Wife
    $w_first = trim($_POST['w_first_name']);
    $w_father = trim($_POST['w_father_name']);
    $w_grandfather = trim($_POST['w_grandfather_name']);
    $w_dob = $_POST['w_date_of_birth'];
    $w_pob = trim($_POST['w_place_of_birth']);
    $w_nationality = trim($_POST['w_nationality']);

    // Divorce Details
    $place_of_divorce = trim($_POST['place_of_divorce']);
    $date_of_divorce = $_POST['date_of_divorce'];

    $cert_num = "DV-" . rand(1000, 9999) . "-" . date('Y');
    $registrar_id = $_SESSION['user_id'];
    $registered_date = date('Y-m-d');

    try {
        $conn->beginTransaction();

        // Insert Husband
        $stmt = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, marital_status) VALUES (?,?,?,?,?,?,?,'Divorced')");
        $stmt->execute([$h_first, $h_father, $h_grandfather, 'Male', $h_dob, $h_pob, $h_nationality]);
        $husband_id = $conn->lastInsertId();

        // Insert Wife
        $stmt->execute([$w_first, $w_father, $w_grandfather, 'Female', $w_dob, $w_pob, $w_nationality]);
        $wife_id = $conn->lastInsertId();

        // Insert Divorce Certificate
        $stmt = $conn->prepare("INSERT INTO divorce_certificates (certificate_number, husband_id, wife_id, place_of_divorce, date_of_divorce, registrar_id, registered_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$cert_num, $husband_id, $wife_id, $place_of_divorce, $date_of_divorce, $registrar_id, $registered_date]);
        $cert_id = $conn->lastInsertId();

        $conn->commit();
        header("Location: print_divorce.php?id=" . $cert_id);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
}

// Fetch all
$certs = $conn->query("SELECT dv.*, 
    h.first_name as h_first, h.father_name as h_father, h.grandfather_name as h_grand,
    w.first_name as w_first, w.father_name as w_father, w.grandfather_name as w_grand
    FROM divorce_certificates dv 
    JOIN persons h ON dv.husband_id = h.id 
    JOIN persons w ON dv.wife_id = w.id 
    ORDER BY dv.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Divorces - Kebele Management System</title>
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
            <h2>Divorce Certificate Registration</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="card-table" style="margin-bottom:25px;">
                <h3>Register Divorce</h3>
                <form method="POST">
                    <div class="form-grid">
                        <!-- DIVORCEE (Husband) -->
                        <div class="section-title">የተፋቺው / Divorcée (Husband)</div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="h_first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="h_father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Grandfather's Name</label>
                            <input type="text" name="h_grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="h_date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="h_place_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="h_nationality" class="form-control" value="Ethiopian">
                        </div>

                        <!-- DIVORCEE (Wife) -->
                        <div class="section-title">የተፋቺዋ / Divorcée (Wife)</div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="w_first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="w_father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Grandfather's Name</label>
                            <input type="text" name="w_grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="w_date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="w_place_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="w_nationality" class="form-control" value="Ethiopian">
                        </div>

                        <!-- Divorce Info -->
                        <div class="section-title">Divorce Details</div>
                        <div class="form-group">
                            <label>Date of Divorce</label>
                            <input type="date" name="date_of_divorce" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Divorce</label>
                            <input type="text" name="place_of_divorce" class="form-control" required>
                        </div>

                        <div class="form-group" style="grid-column:1/-1; margin-top:15px;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Register & Print Certificate</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-table">
                <h3>Divorce Certificates (<?php echo count($certs); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Cert #</th>
                                <th>Husband</th>
                                <th>Wife</th>
                                <th>Date of Divorce</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($certs) > 0): foreach($certs as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['certificate_number']); ?></td>
                                <td><?php echo htmlspecialchars($c['h_first'].' '.$c['h_father'].' '.$c['h_grand']); ?></td>
                                <td><?php echo htmlspecialchars($c['w_first'].' '.$c['w_father'].' '.$c['w_grand']); ?></td>
                                <td><?php echo $c['date_of_divorce']; ?></td>
                                <td>
                                    <a href="print_divorce.php?id=<?php echo $c['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-print"></i></a>
                                    <a href="divorces.php?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" style="text-align:center;">No divorce certificates found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
