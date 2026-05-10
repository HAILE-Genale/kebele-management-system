<?php
// index.php - Landing Page for Bekke Agalo Kebele
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bekke Agalo Kebele - Database Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b3d91 0%, #1a5cb5 40%, #0d47a1 100%);
            display: flex; flex-direction: column; overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l30 30-30 30L0 30z' fill='rgba(255,255,255,0.02)'/%3E%3C/svg%3E") repeat;
            z-index: 0;
        }
        .landing-container {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            position: relative; z-index: 1; padding: 40px 20px;
        }
        .flag-bar {
            width: 100%; height: 6px;
            background: linear-gradient(90deg, #009A44 33.33%, #FED100 33.33% 66.66%, #EF3340 66.66%);
            position: fixed; top: 0; z-index: 100;
        }
        .flag-img {
            width: 120px; height: auto; border-radius: 6px;
            margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            animation: fadeInDown 0.8s ease;
        }
        .landing-title {
            color: #fff; font-size: 2.8em; font-weight: 800;
            text-align: center; margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }
        .landing-subtitle {
            color: rgba(255,255,255,0.85); font-size: 1.3em; font-weight: 400;
            text-align: center; margin-bottom: 10px; animation: fadeInDown 1.1s ease;
        }
        .landing-kebele {
            color: #FED100; font-size: 1.1em; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            margin-bottom: 40px; animation: fadeInDown 1.2s ease;
        }
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; max-width: 900px; width: 100%;
            margin-bottom: 45px; animation: fadeInUp 1.3s ease;
        }
        .feature-card {
            background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 14px;
            padding: 28px 20px; text-align: center; color: #fff;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .feature-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.18); }
        .feature-card i { font-size: 2em; margin-bottom: 12px; color: #FED100; }
        .feature-card h4 { font-size: 1em; font-weight: 600; margin-bottom: 6px; }
        .feature-card p { font-size: 0.8em; color: rgba(255,255,255,0.65); line-height: 1.4; }
        .cta-btn {
            display: inline-flex; align-items: center; gap: 12px;
            padding: 18px 50px; background: #fff; color: #0b3d91;
            font-size: 1.15em; font-weight: 700; border: none; border-radius: 50px;
            cursor: pointer; text-decoration: none;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 1.5s ease;
        }
        .cta-btn:hover { transform: translateY(-3px) scale(1.03); box-shadow: 0 12px 40px rgba(0,0,0,0.35); }
        .secondary-btn {
            display: inline-block; margin-top: 15px;
            color: rgba(255,255,255,0.7); font-size: 0.9em; text-decoration: none;
            transition: color 0.3s; animation: fadeInUp 1.6s ease;
        }
        .secondary-btn:hover { color: #fff; }
        .landing-footer {
            text-align: center; padding: 20px;
            color: rgba(255,255,255,0.4); font-size: 0.8em;
            position: relative; z-index: 1;
        }
        .amharic-text { font-size: 1.5em; color: rgba(255,255,255,0.9); margin-bottom: 5px; animation: fadeInDown 0.9s ease; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .particles { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .particle { position: absolute; width: 4px; height: 4px; background: rgba(255,255,255,0.15); border-radius: 50%; animation: float linear infinite; }
        @keyframes float { 0% { transform: translateY(100vh) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; } }
        @media (max-width: 600px) { .landing-title { font-size: 1.8em; } .landing-subtitle { font-size: 1em; } .features-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
    <div class="flag-bar"></div>
    <div class="particles" id="particles"></div>

    <div class="landing-container">
        <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" class="flag-img">

        <div class="amharic-text">እንኳን ደህና መጡ</div>
        <h1 class="landing-title">Welcome to Bekke Agalo Kebele</h1>
        <p class="landing-subtitle">Civil Database Management System</p>
        <p class="landing-kebele">Federal Democratic Republic of Ethiopia · Vital Event Records</p>

        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-baby"></i>
                <h4>Birth Records</h4>
                <p>Manage and certify birth records in the kebele</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-cross"></i>
                <h4>Death Records</h4>
                <p>Record and issue death certificates</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-ring"></i>
                <h4>Marriage Records</h4>
                <p>Manage official marriage records</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-file-contract"></i>
                <h4>Divorce Records</h4>
                <p>Process and manage divorce records</p>
            </div>
        </div>

        <a href="login.php" class="cta-btn"><i class="fas fa-sign-in-alt"></i> Login to System</a>
        <a href="#" class="secondary-btn">Authorized personnel only</a>
    </div>

    <footer class="landing-footer">
        &copy; <?php echo date('Y'); ?> Bekke Agalo Kebele Administration · All Rights Reserved
    </footer>

    <script>
        const container = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const p = document.createElement('div');
            p.classList.add('particle');
            p.style.left = Math.random() * 100 + '%';
            p.style.width = p.style.height = (Math.random() * 4 + 2) + 'px';
            p.style.animationDuration = (Math.random() * 15 + 10) + 's';
            p.style.animationDelay = (Math.random() * 10) + 's';
            container.appendChild(p);
        }
    </script>
</body>
</html>
