<?php
require_once '../../config/auth.php';
require_once '../../config/db_config.php';

$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'PANITIA') {
        header('Location: panitia_dashboard.php');
    } elseif ($role === 'PESERTA') {
        header('Location: peserta_dashboard.php');
    } elseif ($role === 'JURI') {
        header('Location: juri_dashboard.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'PESERTA';
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                loginUser($user['id'], $user['email'], $user['role']);
                
                // Redirect based on role
                if ($user['role'] === 'PANITIA') {
                    header('Location: panitia_dashboard.php');
                } elseif ($user['role'] === 'PESERTA') {
                    header('Location: peserta_dashboard.php');
                } elseif ($user['role'] === 'JURI') {
                    header('Location: juri_dashboard.php');
                }
                exit;
            } else {
                $error = 'Email atau password salah!';
            }
        } catch (\PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    } else {
        $error = 'Email dan password harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Coding Day 2026</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/coding-day-app/public/css/style.css">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d1117 0%, #161b22 50%, #1f2937 100%);
        }
        .login-card {
            max-width: 450px;
            width: 100%;
            padding: 2.5rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="login-header">
                <i class="bi bi-code-slash" style="font-size: 3rem; color: var(--accent-blue);"></i>
                <h1>CODING DAY 2026</h1>
                <p>Masuk ke Dashboard Anda</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i> Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div class="invalid-feedback">Masukkan email yang valid</div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="••••••••">
                    <div class="invalid-feedback">Masukkan password</div>
                </div>
                
                <div class="mb-4">
                    <label for="role" class="form-label">
                        <i class="bi bi-person-badge me-1"></i> Role
                    </label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="PESERTA" <?= ($_POST['role'] ?? '') === 'PESERTA' ? 'selected' : '' ?>>
                            👨‍💻 Peserta
                        </option>
                        <option value="PANITIA" <?= ($_POST['role'] ?? '') === 'PANITIA' ? 'selected' : '' ?>>
                            🛡️ Panitia
                        </option>
                        <option value="JURI" <?= ($_POST['role'] ?? '') === 'JURI' ? 'selected' : '' ?>>
                            ⭐ Juri
                        </option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="index.php" class="text-decoration-none" style="color: var(--accent-blue);">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Homepage
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
