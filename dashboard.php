<?php
// dashboard.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch basic stats
function getCount($conn, $table) {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
    return $stmt->fetch()['total'];
}

$total_persons = getCount($conn, 'persons');
$total_births = getCount($conn, 'birth_certificates');
$total_deaths = getCount($conn, 'death_certificates');
$total_marriages = getCount($conn, 'marriage_certificates');
$total_divorces = getCount($conn, 'divorce_certificates');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>CIVIL REGISTRY</h3>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="persons.php"><i class="fas fa-users"></i> Citizens</a></li>
            <li><a href="births.php"><i class="fas fa-baby"></i> Births</a></li>
            <li><a href="deaths.php"><i class="fas fa-bed"></i> Deaths</a></li>
            <li><a href="marriages.php"><i class="fas fa-ring"></i> Marriages</a></li>
            <li><a href="divorces.php"><i class="fas fa-file-contract"></i> Divorces</a></li>
            <li><a href="generate.php"><i class="fas fa-print"></i> Generate Certificate</a></li>
            <?php if(isSuperAdmin()): ?>
            <li><a href="admin/users.php"><i class="fas fa-user-shield"></i> User Management</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <h2>Dashboard Overview</h2>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="logout.php" class="btn btn-primary" style="padding: 6px 12px; margin-left: 15px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Citizens Registered</h3>
                    <div class="value"><?php echo number_format($total_persons); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Birth Certificates</h3>
                    <div class="value"><?php echo number_format($total_births); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Death Certificates</h3>
                    <div class="value"><?php echo number_format($total_deaths); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Marriage Certificates</h3>
                    <div class="value"><?php echo number_format($total_marriages); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Divorce Certificates</h3>
                    <div class="value"><?php echo number_format($total_divorces); ?></div>
                </div>
            </div>

            <div class="card-table">
                <h3>Recent Registrations (Citizens)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Date of Birth</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT * FROM persons ORDER BY created_at DESC LIMIT 5");
                            if($recent->rowCount() > 0):
                                while($row = $recent->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['father_name'] . ' ' . $row['grandfather_name']); ?></td>
                                <td><?php echo $row['sex']; ?></td>
                                <td><?php echo $row['date_of_birth']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No citizens registered yet.</td>
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
