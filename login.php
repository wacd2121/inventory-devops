<?php
// login.php

$page_title = "Sign In";
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header("Location: " . base_url('dashboard.php'));
    exit;
}

$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            header("Location: " . base_url('dashboard.php'));
            exit;
        } else {
            $error_msg = "Invalid email or password.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="login-wrapper">
    <div class="card login-card">
        <div class="login-header">
            <i class="fa-solid fa-boxes-stacked login-logo"></i>
            <h1 class="login-title">TileFlow</h1>
            <p style="color: var(--text-secondary); margin-top: 0.25rem;">Sign in to your dashboard</p>
        </div>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo base_url('login.php'); ?>" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control form-control-icon" placeholder="admin@inventory.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control form-control-icon" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
