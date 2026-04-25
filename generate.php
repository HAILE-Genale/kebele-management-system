<?php
// generate.php - Certificate Lookup & Reprint Hub
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$results = [];
$type = '';
$search = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['type']) && isset($_GET['search'])) {
    $type = $_GET['type'];
    $search = trim($_GET['search']);
    $like = "%$search%";

    if ($type == 'birth') {
        $stmt = $conn->prepare("SELECT b.id, b.certificate_number, p.first_name, p.father_name, p.grandfather_name, b.registered_date 
            FROM birth_certificates b JOIN persons p ON b.person_id = p.id 
            WHERE p.first_name LIKE ? OR p.father_name LIKE ? OR b.certificate_number LIKE ?
            ORDER BY b.created_at DESC");
        $stmt->execute([$like, $like, $like]);
    } elseif ($type == 'death') {
        $stmt = $conn->prepare("SELECT d.id, d.certificate_number, p.first_name, p.father_name, p.grandfather_name, d.registered_date 
            FROM death_certificates d JOIN persons p ON d.person_id = p.id 
            WHERE p.first_name LIKE ? OR p.father_name LIKE ? OR d.certificate_number LIKE ?
            ORDER BY d.created_at DESC");
        $stmt->execute([$like, $like, $like]);
    } elseif ($type == 'marriage') {
        $stmt = $conn->prepare("SELECT m.id, m.certificate_number, 
            h.first_name as h_first, h.father_name as h_father,
            w.first_name as w_first, w.father_name as w_father, m.registered_date
            FROM marriage_certificates m 
            JOIN persons h ON m.husband_id = h.id JOIN persons w ON m.wife_id = w.id
            WHERE h.first_name LIKE ? OR w.first_name LIKE ? OR m.certificate_number LIKE ?
            ORDER BY m.created_at DESC");
        $stmt->execute([$like, $like, $like]);
    } elseif ($type == 'divorce') {
        $stmt = $conn->prepare("SELECT dv.id, dv.certificate_number, 
            h.first_name as h_first, h.father_name as h_father,
            w.first_name as w_first, w.father_name as w_father, dv.registered_date
            FROM divorce_certificates dv 
            JOIN persons h ON dv.husband_id = h.id JOIN persons w ON dv.wife_id = w.id
            WHERE h.first_name LIKE ? OR w.first_name LIKE ? OR dv.certificate_number LIKE ?
            ORDER BY dv.created_at DESC");
        $stmt->execute([$like, $like, $like]);
    }

    if (isset($stmt)) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Certificate - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-sm { padding: 5px 10px; font-size: 0.85em; width: auto; display: inline-block; }
        .btn-info { background: #5bc0de; color: #fff; }
        .search-form { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .search-form select, .search-form input { flex: 1; min-width: 200px; }
        .search-form button { width: auto; padding: 12px 30px; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .quick-link-card { background: #fff; border-radius: 8px; padding: 25px; text-align: center; text-decoration: none; color: var(--text-primary); box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid var(--primary-color); }
        .quick-link-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .quick-link-card i { font-size: 2em; color: var(--primary-color); margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h2><i class="fas fa-certificate"></i> Certificate Generation & Lookup</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">
            <!-- Quick Registration Links -->
            <div class="quick-links">
                <a href="births.php" class="quick-link-card">
                    <i class="fas fa-baby"></i>
                    <strong>Register Birth</strong>
                    <p style="color:#888; font-size:0.85em;">New birth certificate</p>
                </a>
                <a href="deaths.php" class="quick-link-card">
                    <i class="fas fa-cross"></i>
                    <strong>Register Death</strong>
                    <p style="color:#888; font-size:0.85em;">New death certificate</p>
                </a>
                <a href="marriages.php" class="quick-link-card">
                    <i class="fas fa-ring"></i>
                    <strong>Register Marriage</strong>
                    <p style="color:#888; font-size:0.85em;">New marriage certificate</p>
                </a>
                <a href="divorces.php" class="quick-link-card">
                    <i class="fas fa-file-contract"></i>
                    <strong>Register Divorce</strong>
                    <p style="color:#888; font-size:0.85em;">New divorce certificate</p>
                </a>
            </div>

            <!-- Search Existing Certificates -->
            <div class="card-table" style="margin-bottom:25px;">
                <h3><i class="fas fa-search"></i> Search & Reprint Existing Certificates</h3>
                <form method="GET" class="search-form" style="margin-top:15px;">
                    <select name="type" class="form-control" required>
                        <option value="">-- Select Certificate Type --</option>
                        <option value="birth" <?php echo $type=='birth'?'selected':''; ?>>Birth Certificate</option>
                        <option value="death" <?php echo $type=='death'?'selected':''; ?>>Death Certificate</option>
                        <option value="marriage" <?php echo $type=='marriage'?'selected':''; ?>>Marriage Certificate</option>
                        <option value="divorce" <?php echo $type=='divorce'?'selected':''; ?>>Divorce Certificate</option>
                    </select>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or certificate number..." value="<?php echo htmlspecialchars($search); ?>" required>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <!-- Results -->
            <?php if(!empty($type) && !empty($search)): ?>
            <div class="card-table">
                <h3>Search Results (<?php echo count($results); ?> found)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>Name(s)</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($results) > 0): foreach($results as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['certificate_number']); ?></td>
                                <td>
                                    <?php
                                    if ($type == 'birth' || $type == 'death') {
                                        echo htmlspecialchars($r['first_name'].' '.$r['father_name'].' '.$r['grandfather_name']);
                                    } else {
                                        echo htmlspecialchars($r['h_first'].' '.$r['h_father']) . ' & ' . htmlspecialchars($r['w_first'].' '.$r['w_father']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo $r['registered_date']; ?></td>
                                <td>
                                    <a href="print_<?php echo $type; ?>.php?id=<?php echo $r['id']; ?>" class="btn btn-info btn-sm" target="_blank"><i class="fas fa-print"></i> Print</a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" style="text-align:center;">No certificates found matching your search.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
