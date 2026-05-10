<?php
// analytics.php - Data Analysis with Charts & Graphs
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// === GATHER ALL ANALYTICS DATA ===

// 1. Total counts
$total_persons = $conn->query("SELECT COUNT(*) as c FROM persons")->fetch()['c'];
$total_births = $conn->query("SELECT COUNT(*) as c FROM birth_certificates")->fetch()['c'];
$total_deaths = $conn->query("SELECT COUNT(*) as c FROM death_certificates")->fetch()['c'];
$total_marriages = $conn->query("SELECT COUNT(*) as c FROM marriage_certificates")->fetch()['c'];
$total_divorces = $conn->query("SELECT COUNT(*) as c FROM divorce_certificates")->fetch()['c'];

// 2. Gender distribution
$male_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE sex='Male'")->fetch()['c'];
$female_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE sex='Female'")->fetch()['c'];

// 3. Marital status distribution
$single_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE marital_status='Single'")->fetch()['c'];
$married_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE marital_status='Married'")->fetch()['c'];
$divorced_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE marital_status='Divorced'")->fetch()['c'];
$widowed_count = $conn->query("SELECT COUNT(*) as c FROM persons WHERE marital_status='Widowed'")->fetch()['c'];

// 4. Monthly registration trends (last 12 months)
$monthly_births = [];
$monthly_deaths = [];
$monthly_marriages = [];
$monthly_divorces = [];
$month_labels = [];

for ($i = 11; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime("-$i months"));
    $month_labels[] = date('M Y', strtotime("-$i months"));

    $b = $conn->prepare("SELECT COUNT(*) as c FROM birth_certificates WHERE registered_date BETWEEN ? AND ?");
    $b->execute([$month_start, $month_end]);
    $monthly_births[] = (int)$b->fetch()['c'];

    $d = $conn->prepare("SELECT COUNT(*) as c FROM death_certificates WHERE registered_date BETWEEN ? AND ?");
    $d->execute([$month_start, $month_end]);
    $monthly_deaths[] = (int)$d->fetch()['c'];

    $m = $conn->prepare("SELECT COUNT(*) as c FROM marriage_certificates WHERE registered_date BETWEEN ? AND ?");
    $m->execute([$month_start, $month_end]);
    $monthly_marriages[] = (int)$m->fetch()['c'];

    $dv = $conn->prepare("SELECT COUNT(*) as c FROM divorce_certificates WHERE registered_date BETWEEN ? AND ?");
    $dv->execute([$month_start, $month_end]);
    $monthly_divorces[] = (int)$dv->fetch()['c'];
}

// 5. Age distribution of registered persons
$age_groups = [
    '0-5' => 0, '6-17' => 0, '18-30' => 0, '31-45' => 0, '46-60' => 0, '60+' => 0
];
$persons_ages = $conn->query("SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age FROM persons")->fetchAll(PDO::FETCH_ASSOC);
foreach ($persons_ages as $p) {
    $age = (int)$p['age'];
    if ($age <= 5) $age_groups['0-5']++;
    elseif ($age <= 17) $age_groups['6-17']++;
    elseif ($age <= 30) $age_groups['18-30']++;
    elseif ($age <= 45) $age_groups['31-45']++;
    elseif ($age <= 60) $age_groups['46-60']++;
    else $age_groups['60+']++;
}

// 6. Nationality distribution (top 5)
$nationalities = $conn->query("SELECT nationality, COUNT(*) as c FROM persons GROUP BY nationality ORDER BY c DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$nat_labels = array_column($nationalities, 'nationality');
$nat_values = array_column($nationalities, 'c');

// 7. Daily registrations (last 30 days)
$daily_labels = [];
$daily_counts = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $daily_labels[] = date('d M', strtotime("-$i days"));
    $c = $conn->prepare("SELECT COUNT(*) as c FROM persons WHERE DATE(created_at) = ?");
    $c->execute([$day]);
    $daily_counts[] = (int)$c->fetch()['c'];
}

// 8. Registrar performance
$registrar_stats = $conn->query("
    SELECT u.name, 
        (SELECT COUNT(*) FROM birth_certificates WHERE registrar_id = u.id) +
        (SELECT COUNT(*) FROM death_certificates WHERE registrar_id = u.id) +
        (SELECT COUNT(*) FROM marriage_certificates WHERE registrar_id = u.id) +
        (SELECT COUNT(*) FROM divorce_certificates WHERE registrar_id = u.id) as total_certs
    FROM users u ORDER BY total_certs DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 9. Educational level distribution
$edu_levels_list = ['No Formal Education','Primary (1-8)','Secondary (9-12)','Certificate/Diploma',"Bachelor's Degree","Master's Degree",'PhD/Doctorate'];
$edu_counts = [];
foreach ($edu_levels_list as $lvl) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM persons WHERE educational_level = ?");
    $stmt->execute([$lvl]);
    $edu_counts[] = (int)$stmt->fetch()['c'];
}

// 10. Occupational status distribution
$occ_statuses_list = ['Employed','Self-Employed','Unemployed','Student','Retired','Farmer','Housewife','Other'];
$occ_counts = [];
foreach ($occ_statuses_list as $occ) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM persons WHERE occupational_status = ?");
    $stmt->execute([$occ]);
    $occ_counts[] = (int)$stmt->fetch()['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 25px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .chart-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .chart-card h3 {
            font-size: 1.05em;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .chart-card h3 i { opacity: 0.7; }
        .chart-container {
            position: relative;
            width: 100%;
            height: 280px;
        }
        .full-width { grid-column: 1 / -1; }
        .full-width .chart-container { height: 350px; }

        /* Summary strip */
        .summary-strip {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        .summary-item::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
        }
        .summary-item:nth-child(1)::before { background: linear-gradient(90deg, #4facfe, #00f2fe); }
        .summary-item:nth-child(2)::before { background: linear-gradient(90deg, #43e97b, #38f9d7); }
        .summary-item:nth-child(3)::before { background: linear-gradient(90deg, #fa709a, #fee140); }
        .summary-item:nth-child(4)::before { background: linear-gradient(90deg, #a18cd1, #fbc2eb); }
        .summary-item:nth-child(5)::before { background: linear-gradient(90deg, #fccb90, #d57eeb); }
        .summary-item .number {
            font-size: 2.4em;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), #1e90ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .summary-item .label {
            font-size: 0.85em;
            color: var(--text-secondary);
            margin-top: 5px;
            font-weight: 500;
        }
        .summary-item .icon {
            font-size: 1.4em;
            margin-bottom: 8px;
            color: var(--primary-color);
            opacity: 0.6;
        }

        /* Registrar Table */
        .perf-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .perf-table th, .perf-table td { padding: 10px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .perf-table th { color: var(--text-secondary); font-size: 0.85em; text-transform: uppercase; }
        .perf-bar { height: 8px; border-radius: 4px; background: linear-gradient(90deg, #4facfe, #00f2fe); }

        @media (max-width: 900px) {
            .summary-strip { grid-template-columns: repeat(2, 1fr); }
            .analytics-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h2><i class="fas fa-chart-line"></i> Analytics & Reports</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary" style="padding:6px 12px; margin-left:10px; width:auto;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-wrapper">

            <!-- Summary Strip -->
            <div class="summary-strip">
                <div class="summary-item">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="number"><?php echo number_format($total_persons); ?></div>
                    <div class="label">Total Citizens</div>
                </div>
                <div class="summary-item">
                    <div class="icon"><i class="fas fa-baby"></i></div>
                    <div class="number"><?php echo number_format($total_births); ?></div>
                    <div class="label">Birth Certificates</div>
                </div>
                <div class="summary-item">
                    <div class="icon"><i class="fas fa-cross"></i></div>
                    <div class="number"><?php echo number_format($total_deaths); ?></div>
                    <div class="label">Death Certificates</div>
                </div>
                <div class="summary-item">
                    <div class="icon"><i class="fas fa-ring"></i></div>
                    <div class="number"><?php echo number_format($total_marriages); ?></div>
                    <div class="label">Marriage Certificates</div>
                </div>
                <div class="summary-item">
                    <div class="icon"><i class="fas fa-file-contract"></i></div>
                    <div class="number"><?php echo number_format($total_divorces); ?></div>
                    <div class="label">Divorce Certificates</div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="analytics-grid">

                <!-- 1. Certificate Distribution (Doughnut) -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Certificate Distribution</h3>
                    <div class="chart-container">
                        <canvas id="certDistChart"></canvas>
                    </div>
                </div>

                <!-- 2. Gender Distribution (Doughnut) -->
                <div class="chart-card">
                    <h3><i class="fas fa-venus-mars"></i> Gender Distribution</h3>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>

                <!-- 3. Monthly Trends (Line Chart - Full Width) -->
                <div class="chart-card full-width">
                    <h3><i class="fas fa-chart-line"></i> Monthly Registration Trends (Last 12 Months)</h3>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>

                <!-- 4. Age Distribution (Bar Chart) -->
                <div class="chart-card">
                    <h3><i class="fas fa-birthday-cake"></i> Age Group Distribution</h3>
                    <div class="chart-container">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>

                <!-- 5. Marital Status (Polar Area) -->
                <div class="chart-card">
                    <h3><i class="fas fa-heart"></i> Marital Status Breakdown</h3>
                    <div class="chart-container">
                        <canvas id="maritalChart"></canvas>
                    </div>
                </div>

                <!-- 6. Daily Registrations (Area Chart - Full Width) -->
                <div class="chart-card full-width">
                    <h3><i class="fas fa-calendar-day"></i> Daily Citizen Registrations (Last 30 Days)</h3>
                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>

                <!-- 7. Nationality (Horizontal Bar) -->
                <div class="chart-card">
                    <h3><i class="fas fa-globe-africa"></i> Top Nationalities</h3>
                    <div class="chart-container">
                        <canvas id="natChart"></canvas>
                    </div>
                </div>

                <!-- 8. Registrar Performance Table -->
                <div class="chart-card">
                    <h3><i class="fas fa-user-tie"></i> Registrar Performance</h3>
                    <table class="perf-table">
                        <thead>
                            <tr><th>Registrar</th><th>Certificates Issued</th><th>Activity</th></tr>
                        </thead>
                        <tbody>
                        <?php
                        $max_certs = max(array_column($registrar_stats, 'total_certs') ?: [1]);
                        foreach($registrar_stats as $rs):
                            $pct = $max_certs > 0 ? ($rs['total_certs'] / $max_certs) * 100 : 0;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rs['name']); ?></td>
                                <td><strong><?php echo $rs['total_certs']; ?></strong></td>
                                <td><div class="perf-bar" style="width:<?php echo $pct; ?>%;"></div></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($registrar_stats)): ?>
                            <tr><td colspan="3" style="text-align:center; color:#888;">No data yet</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 9. Educational Level Distribution (Bar Chart) -->
                <div class="chart-card">
                    <h3><i class="fas fa-graduation-cap"></i> Educational Level Distribution</h3>
                    <div class="chart-container">
                        <canvas id="eduChart"></canvas>
                    </div>
                </div>

                <!-- 10. Occupational Status Distribution (Bar Chart) -->
                <div class="chart-card">
                    <h3><i class="fas fa-briefcase"></i> Occupational Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="occChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
    // === COLOR PALETTES ===
    const vibrant = ['#4facfe','#43e97b','#fa709a','#a18cd1','#fccb90','#f093fb'];
    const gradientBlue = (ctx) => {
        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
        g.addColorStop(0, 'rgba(79,172,254,0.4)');
        g.addColorStop(1, 'rgba(79,172,254,0.02)');
        return g;
    };

    // Common options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { font: { family: "'Inter', sans-serif", size: 12 }, padding: 15, usePointStyle: true }
            }
        }
    };

    // 1. Certificate Distribution (Doughnut)
    new Chart(document.getElementById('certDistChart'), {
        type: 'doughnut',
        data: {
            labels: ['Births', 'Deaths', 'Marriages', 'Divorces'],
            datasets: [{
                data: [<?php echo "$total_births, $total_deaths, $total_marriages, $total_divorces"; ?>],
                backgroundColor: ['#4facfe','#fa709a','#43e97b','#a18cd1'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            ...defaultOptions,
            cutout: '65%',
            plugins: {
                ...defaultOptions.plugins,
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    cornerRadius: 8
                }
            }
        }
    });

    // 2. Gender Distribution (Doughnut)
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [<?php echo "$male_count, $female_count"; ?>],
                backgroundColor: ['#4facfe','#fa709a'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: { ...defaultOptions, cutout: '65%' }
    });

    // 3. Monthly Trends (Line)
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [
                {
                    label: 'Births',
                    data: <?php echo json_encode($monthly_births); ?>,
                    borderColor: '#4facfe',
                    backgroundColor: 'rgba(79,172,254,0.1)',
                    tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5
                },
                {
                    label: 'Deaths',
                    data: <?php echo json_encode($monthly_deaths); ?>,
                    borderColor: '#fa709a',
                    backgroundColor: 'rgba(250,112,154,0.1)',
                    tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5
                },
                {
                    label: 'Marriages',
                    data: <?php echo json_encode($monthly_marriages); ?>,
                    borderColor: '#43e97b',
                    backgroundColor: 'rgba(67,233,123,0.1)',
                    tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5
                },
                {
                    label: 'Divorces',
                    data: <?php echo json_encode($monthly_divorces); ?>,
                    borderColor: '#a18cd1',
                    backgroundColor: 'rgba(161,140,209,0.1)',
                    tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5
                }
            ]
        },
        options: {
            ...defaultOptions,
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 4. Age Distribution (Bar)
    new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($age_groups)); ?>,
            datasets: [{
                label: 'Citizens',
                data: <?php echo json_encode(array_values($age_groups)); ?>,
                backgroundColor: vibrant,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 35
            }]
        },
        options: {
            ...defaultOptions,
            plugins: { ...defaultOptions.plugins, legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 5. Marital Status (Polar Area)
    new Chart(document.getElementById('maritalChart'), {
        type: 'polarArea',
        data: {
            labels: ['Single', 'Married', 'Divorced', 'Widowed'],
            datasets: [{
                data: [<?php echo "$single_count, $married_count, $divorced_count, $widowed_count"; ?>],
                backgroundColor: ['rgba(79,172,254,0.7)','rgba(67,233,123,0.7)','rgba(161,140,209,0.7)','rgba(250,112,154,0.7)'],
                borderWidth: 0
            }]
        },
        options: { ...defaultOptions }
    });

    // 6. Daily Registrations (Area)
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($daily_labels); ?>,
            datasets: [{
                label: 'Citizen Registrations',
                data: <?php echo json_encode($daily_counts); ?>,
                borderColor: '#4facfe',
                backgroundColor: gradientBlue,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#4facfe',
                borderWidth: 2.5
            }]
        },
        options: {
            ...defaultOptions,
            plugins: { ...defaultOptions.plugins, legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false }, ticks: { maxRotation: 45 } }
            }
        }
    });

    // 7. Nationality (Horizontal Bar)
    new Chart(document.getElementById('natChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nat_labels); ?>,
            datasets: [{
                label: 'Citizens',
                data: <?php echo json_encode(array_map('intval', $nat_values)); ?>,
                backgroundColor: vibrant,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 28
            }]
        },
        options: {
            ...defaultOptions,
            indexAxis: 'y',
            plugins: { ...defaultOptions.plugins, legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });

    // 9. Educational Level Distribution (Horizontal Bar)
    new Chart(document.getElementById('eduChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($edu_levels_list); ?>,
            datasets: [{
                label: 'Citizens',
                data: <?php echo json_encode($edu_counts); ?>,
                backgroundColor: [
                    'rgba(79,172,254,0.8)','rgba(67,233,123,0.8)','rgba(250,112,154,0.8)',
                    'rgba(161,140,209,0.8)','rgba(252,203,144,0.8)','rgba(240,147,43,0.8)',
                    'rgba(99,205,218,0.8)'
                ],
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 22
            }]
        },
        options: {
            ...defaultOptions,
            indexAxis: 'y',
            plugins: { ...defaultOptions.plugins, legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // 10. Occupational Status Distribution (Doughnut)
    new Chart(document.getElementById('occChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($occ_statuses_list); ?>,
            datasets: [{
                data: <?php echo json_encode($occ_counts); ?>,
                backgroundColor: [
                    '#4facfe','#43e97b','#fa709a','#a18cd1',
                    '#fccb90','#f093fb','#fd7043','#26c6da'
                ],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            ...defaultOptions,
            cutout: '55%',
            plugins: {
                ...defaultOptions.plugins,
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    cornerRadius: 8
                }
            }
        }
    });
    </script>
</body>
</html>
