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
    <title>Dashboard - Bekke Agalo Kebele</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card a { text-decoration: none; color: inherit; display: block; }
        .stat-card { cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-card .view-link { font-size: 0.8em; color: var(--primary-color); margin-top: 8px; display: block; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <h2>Dashboard Overview</h2>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="logout.php" class="btn btn-primary" style="padding: 6px 16px; margin-left: 25px; width: auto; display: inline-block; font-size: 0.85em;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <a href="persons.php">
                        <h3><i class="fas fa-users"></i> Total Kebele Members</h3>
                        <div class="value"><?php echo number_format($total_persons); ?></div>
                        <span class="view-link">View all members →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="births.php">
                        <h3><i class="fas fa-baby"></i> Birth Certificates</h3>
                        <div class="value"><?php echo number_format($total_births); ?></div>
                        <span class="view-link">View all births →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="deaths.php">
                        <h3><i class="fas fa-cross"></i> Death Certificates</h3>
                        <div class="value"><?php echo number_format($total_deaths); ?></div>
                        <span class="view-link">View all deaths →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="marriages.php">
                        <h3><i class="fas fa-ring"></i> Marriage Certificates</h3>
                        <div class="value"><?php echo number_format($total_marriages); ?></div>
                        <span class="view-link">View all marriages →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="divorces.php">
                        <h3><i class="fas fa-file-contract"></i> Divorce Certificates</h3>
                        <div class="value"><?php echo number_format($total_divorces); ?></div>
                        <span class="view-link">View all divorces →</span>
                    </a>
                </div>
            </div>

            <div class="card-table">
                <h3>Recent Records</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Date of Birth</th>
                                <th>Date Added</th>
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
                                <td colspan="5" style="text-align: center;">No records yet.</td>
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
