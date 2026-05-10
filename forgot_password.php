<?php
// forgot_password.php - Password Reset Page
require_once 'includes/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    $username  = trim($_POST['username'] ?? '');
    $new_pass  = $_POST['new_password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $admin_pin = $_POST['admin_pin'] ?? '';

    // Simple security: require office admin PIN (change this as needed)
    $OFFICE_PIN = '1234'; // ← Change to your kebele office PIN

    if (empty($username) || empty($new_pass) || empty($confirm) || empty($admin_pin)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif ($admin_pin !== $OFFICE_PIN) {
        $message = 'Incorrect office PIN. Contact your system administrator.';
        $messageType = 'error';
    } elseif ($new_pass !== $confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($new_pass) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        if ($conn) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update->execute([$hashed, $username]);
                $message = 'Password reset successfully! You can now log in.';
                $messageType = 'success';
            } else {
                $message = 'Username not found.';
                $messageType = 'error';
            }
        } else {
            $message = 'Database connection error. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Bekke Agalo Kebele DBMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .kebele-badge { background: linear-gradient(90deg, #009A44 33%, #FED100 33% 66%, #EF3340 66%); height: 4px; border-radius: 2px; margin-bottom: 20px; }
        .login-card h2 { font-size: 1.35em; }
        .login-card p.hint { font-size: 0.82em; color: #999; margin-top: -10px; margin-bottom: 18px; text-align: center; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 18px; font-size: 0.9em; display: flex; align-items: center; gap: 10px; }
        .alert-error { background: #fdecea; color: #c0392b; border-left: 4px solid #c0392b; }
        .alert-success { background: #eafaf1; color: #1e8449; border-left: 4px solid #27ae60; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #888; text-decoration: none; font-size: 0.9em; }
        .back-link:hover { color: var(--primary-color); }
        .pin-note { font-size: 0.78em; color: #aaa; margin-top: 4px; }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 42px; }
        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #aaa; background: none; border: none; font-size: 1em; }
        .toggle-pw:hover { color: var(--primary-color); }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="login-card">
            <div class="kebele-badge"></div>
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            <p class="hint">Verify your identity with the office PIN to reset your password.</p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php if ($messageType === 'success'): ?>
                    <a href="login.php" class="btn btn-primary" style="display:block;text-align:center;text-decoration:none;margin-bottom:10px;">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($messageType !== 'success'): ?>
            <form action="" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                           placeholder="Your account username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-control"
                               placeholder="At least 6 characters" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                               placeholder="Re-enter new password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_pin"><i class="fas fa-shield-alt"></i> Office Admin PIN</label>
                    <input type="password" id="admin_pin" name="admin_pin" class="form-control"
                           placeholder="Enter the office security PIN" required maxlength="10">
                    <p class="pin-note"><i class="fas fa-info-circle"></i> Contact your kebele administrator if you don't have the PIN.</p>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Reset Password
                </button>
            </form>
            <?php endif; ?>

            <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script>
        function togglePw(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
