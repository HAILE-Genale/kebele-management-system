<?php
// admin/users.php - User Management (Super Admin Only)
require_once '../includes/auth.php';
requireSuperAdmin();
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "User deleted successfully.";
    }
}

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->rowCount() > 0) {
            $error = "Username already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)");
            $stmt->execute([$name, $username, $hashed, $role]);
            $success = "User '$username' created successfully with role '$role'.";
        }
    }
}

// Fetch all users
$users = $conn->query("SELECT id, name, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Kebele Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; width: auto; display: inline-block; }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; }
        .badge-admin { background: #d1ecf1; color: #0c5460; }
        .badge-super { background: #fff3cd; color: #856404; }
        /* Override sidebar links for admin subfolder */
        .sidebar a[href] { }
    </style>
</head>
<body>
    <!-- Sidebar (admin subfolder adjusted paths) -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>CIVIL REGISTRY</h3>
            <p style="font-size:0.75em; color:#888; margin-top:5px;">Kebele Management</p>
        </div>
        <ul class="nav-links">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="../persons.php"><i class="fas fa-users"></i> Citizens</a></li>
            <li><a href="../births.php"><i class="fas fa-baby"></i> Births</a></li>
            <li><a href="../deaths.php"><i class="fas fa-cross"></i> Deaths</a></li>
            <li><a href="../marriages.php"><i class="fas fa-ring"></i> Marriages</a></li>
            <li><a href="../divorces.php"><i class="fas fa-file-contract"></i> Divorces</a></li>
            <li><a href="../generate.php"><i class="fas fa-certificate"></i> Certificates</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-user-shield"></i> User Mgmt</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="topbar">
            <h2><i class="fas fa-user-shield"></i> User Management</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?> (Super Admin)</span>
                <a href="../logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <!-- Create User Form -->
            <div class="card-table" style="margin-bottom:25px;">
                <h3>Create New Admin User</h3>
                <form method="POST" style="margin-top:15px;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Abebe Kebede" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. abebe_k" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control">
                                <option value="Admin">Admin (Registrar)</option>
                                <option value="Super Admin">Super Admin</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create User</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="card-table">
                <h3>All Users (<?php echo count($users); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <span class="badge <?php echo $u['role']=='Super Admin' ? 'badge-super' : 'badge-admin'; ?>">
                                        <?php echo $u['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                        <span style="color:#888; font-size:0.85em;">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
