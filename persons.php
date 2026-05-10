<?php
// persons.php - Citizens Management (CRUD)
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM persons WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Citizen record deleted successfully.";
    } catch (Exception $e) {
        $error = "Cannot delete: This person is linked to a certificate record. Delete the certificate first.";
    }
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $father_name = trim($_POST['father_name']);
    $grandfather_name = trim($_POST['grandfather_name']);
    $sex = $_POST['sex'];
    $date_of_birth = $_POST['date_of_birth'];
    $place_of_birth = trim($_POST['place_of_birth']);
    $nationality = trim($_POST['nationality']);
    $marital_status = $_POST['marital_status'];
    $educational_level = $_POST['educational_level'];
    $occupational_status = $_POST['occupational_status'];

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE persons SET first_name=?, father_name=?, grandfather_name=?, sex=?, date_of_birth=?, place_of_birth=?, nationality=?, marital_status=?, educational_level=?, occupational_status=? WHERE id=?");
        $stmt->execute([$first_name, $father_name, $grandfather_name, $sex, $date_of_birth, $place_of_birth, $nationality, $marital_status, $educational_level, $occupational_status, $_POST['edit_id']]);
        $success = "Citizen record updated successfully.";
    } else {
        // CREATE
        $stmt = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, marital_status, educational_level, occupational_status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$first_name, $father_name, $grandfather_name, $sex, $date_of_birth, $place_of_birth, $nationality, $marital_status, $educational_level, $occupational_status]);
        $success = "Citizen registered successfully.";
    }
}

// Fetch edit data if editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM persons WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM persons WHERE first_name LIKE ? OR father_name LIKE ? OR grandfather_name LIKE ? OR id = ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like, $search]);
} else {
    $stmt = $conn->query("SELECT * FROM persons ORDER BY id DESC");
}
$persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizens - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .section-title { grid-column: 1 / -1; margin: 15px 0 5px; border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; color: var(--primary-color); }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; width: auto; display: inline-block; }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .btn-warning { background: #f0ad4e; color: #fff; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { flex: 1; }
        .search-bar button { width: auto; padding: 12px 25px; }
        .badge-active { background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold; }
        .badge-inactive { background: #dc3545; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h2>Citizens Registry</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <!-- Registration / Edit Form -->
            <div class="card-table" style="margin-bottom: 25px;">
                <h3><?php echo $edit_data ? 'Edit Citizen' : 'Register New Citizen'; ?></h3>
                <form method="POST" action="persons.php">
                    <?php if($edit_data): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo $edit_data ? htmlspecialchars($edit_data['first_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?php echo $edit_data ? htmlspecialchars($edit_data['father_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Grandfather's Name</label>
                            <input type="text" name="grandfather_name" class="form-control" value="<?php echo $edit_data ? htmlspecialchars($edit_data['grandfather_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select name="sex" class="form-control" required>
                                <option value="Male" <?php echo ($edit_data && $edit_data['sex']=='Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($edit_data && $edit_data['sex']=='Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?php echo $edit_data ? $edit_data['date_of_birth'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="place_of_birth" class="form-control" value="<?php echo $edit_data ? htmlspecialchars($edit_data['place_of_birth']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="<?php echo $edit_data ? htmlspecialchars($edit_data['nationality']) : 'Ethiopian'; ?>">
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status" class="form-control">
                                <option value="Single" <?php echo ($edit_data && $edit_data['marital_status']=='Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($edit_data && $edit_data['marital_status']=='Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo ($edit_data && $edit_data['marital_status']=='Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo ($edit_data && $edit_data['marital_status']=='Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Educational Level</label>
                            <select name="educational_level" class="form-control">
                                <?php
                                $edu_levels = ['No Formal Education','Primary (1-8)','Secondary (9-12)','Certificate/Diploma',"Bachelor's Degree","Master's Degree",'PhD/Doctorate'];
                                foreach ($edu_levels as $lvl):
                                    $sel = ($edit_data && $edit_data['educational_level']==$lvl) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $lvl; ?>" <?php echo $sel; ?>><?php echo $lvl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Occupational Status</label>
                            <select name="occupational_status" class="form-control">
                                <?php
                                $occ_statuses = ['Employed','Self-Employed','Unemployed','Student','Retired','Farmer','Housewife','Other'];
                                foreach ($occ_statuses as $occ):
                                    $sel = ($edit_data && $edit_data['occupational_status']==$occ) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $occ; ?>" <?php echo $sel; ?>><?php echo $occ; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: 1/-1;">
                            <button type="submit" class="btn btn-primary"><?php echo $edit_data ? 'Update Citizen' : 'Register Citizen'; ?></button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search -->
            <form method="GET" class="search-bar">
                <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <?php if($search): ?><a href="persons.php" class="btn btn-warning btn-sm" style="padding:12px;">Clear</a><?php endif; ?>
            </form>

            <!-- Citizens Table -->
            <div class="card-table">
                <h3>All Citizens (<?php echo count($persons); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Date of Birth</th>
                                <th>Nationality</th>
                                <th>Marital Status</th>
                                <th>Educational Level</th>
                                <th>Occupational Status</th>
                                <th>Activity Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($persons) > 0): foreach($persons as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['first_name'].' '.$p['father_name'].' '.$p['grandfather_name']); ?></td>
                                <td><?php echo $p['sex']; ?></td>
                                <td><?php echo $p['date_of_birth']; ?></td>
                                <td><?php echo htmlspecialchars($p['nationality']); ?></td>
                                <td><?php echo $p['marital_status']; ?></td>
                                <td><?php echo htmlspecialchars($p['educational_level'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($p['occupational_status'] ?? '—'); ?></td>
                                <td>
                                    <?php if(($p['status'] ?? 'Active') == 'Active'): ?>
                                        <span class="badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(($p['status'] ?? 'Active') == 'Active'): ?>
                                        <a href="persons.php?edit=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="persons.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                        <span style="color:#999; font-size:0.85em;"><i class="fas fa-ban"></i> Deceased</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="10" style="text-align:center;">No citizens found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
