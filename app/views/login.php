<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'PANITIA') {
        header('Location: /panitia');
    } elseif ($role === 'PESERTA') {
        header('Location: /peserta');
    } elseif ($role === 'JURI') {
        header('Location: /juri');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'PESERTA';
    
    if (!empty($email)) {
        try {
            // Login hanya dengan email dan role - tanpa password
            $user = $usersCollection->findOne([
                'email' => $email,
                'role' => $role
            ]);
            
            if ($user) {
                loginUser((string)$user['_id'], $user['email'], $user['role']);
                
                // Redirect based on role
                if ($user['role'] === 'PANITIA') {
                    header('Location: /panitia');
                } elseif ($user['role'] === 'PESERTA') {
                    header('Location: /peserta');
                } elseif ($user['role'] === 'JURI') {
                    header('Location: /juri');
                }
                exit;
            } else {
                $error = 'Email tidak ditemukan untuk role yang dipilih!';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    } else {
        $error = 'Email harus diisi!';
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
    <link rel="stylesheet" href="/public/css/style.css">
    
    <style>
        /* LOGIN PAGE SPECIFIC STYLES */
        body {
            background: linear-gradient(135deg, var(--primary-bg) 0%, var(--secondary-bg) 50%, var(--tertiary-bg) 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-white);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .login-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(88, 166, 255, 0.08), transparent);
            border-radius: 50%;
            z-index: 0;
        }

        .login-container::after {
            content: "";
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(137, 87, 229, 0.08), transparent);
            border-radius: 50%;
            z-index: 0;
        }

        .login-card {
            max-width: 480px;
            width: 100%;
            padding: 3rem 2.5rem;
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-bg) 100%);
            border: 2px solid rgba(88, 166, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 40px rgba(88, 166, 255, 0.1);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-icon {
            font-size: 3.5rem;
            color: var(--accent-blue-light);
            margin-bottom: 1rem;
            display: block;
            text-shadow: 0 0 20px rgba(88, 166, 255, 0.3);
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent-blue-light);
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            font-family: 'JetBrains Mono', monospace;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .info-badge {
            display: inline-block;
            background: rgba(88, 166, 255, 0.15);
            color: var(--accent-blue-light);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.75rem;
            border: 1px solid rgba(88, 166, 255, 0.2);
        }

        .form-group-login {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            color: var(--text-white);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--accent-blue-light);
            font-size: 1.1rem;
        }

        .form-control, .form-select {
            background: linear-gradient(135deg, rgba(15, 23, 41, 0.6) 0%, rgba(26, 35, 50, 0.6) 100%);
            border: 2px solid rgba(88, 166, 255, 0.2);
            color: var(--text-white);
            padding: 0.875rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control::placeholder {
            color: var(--text-faint);
        }

        .form-control:focus, .form-select:focus {
            background: linear-gradient(135deg, rgba(15, 23, 41, 0.8) 0%, rgba(26, 35, 50, 0.8) 100%);
            border-color: var(--accent-blue-light);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.15);
            color: var(--text-white);
            outline: none;
        }

        .form-select option {
            background: var(--secondary-bg);
            color: var(--text-white);
            padding: 0.5rem;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-blue-dark) 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 16px rgba(9, 105, 218, 0.3);
            width: 100%;
            margin-bottom: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(9, 105, 218, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.1);
            color: var(--status-danger);
            border-left: 4px solid var(--status-danger);
        }

        .alert-danger i {
            font-size: 1.2rem;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }

        .invalid-feedback {
            color: var(--status-danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(88, 166, 255, 0.1);
        }

        .login-footer a {
            color: var(--accent-blue-light);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .login-footer a:hover {
            color: var(--accent-green-bright);
            gap: 0.75rem;
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 12px;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .login-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-code-slash login-icon"></i>
                <h1>CODING DAY</h1>
                <p>Kompetisi Pemrograman 2026</p>
                <span class="info-badge">
                    <i class="bi bi-shield-check"></i> Secure & Fast
                </span>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" style="position: absolute; right: 1rem; top: 1rem;"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-group-login">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope-fill"></i> Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div class="invalid-feedback">Masukkan email yang valid</div>
                </div>
                
                <div class="form-group-login">
                    <label for="role" class="form-label">
                        <i class="bi bi-person-badge-fill"></i> Pilih Role Anda
                    </label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="PESERTA" <?= ($_POST['role'] ?? '') === 'PESERTA' ? 'selected' : '' ?>>
                            👨‍💻 Peserta - Kompetitor
                        </option>
                        <option value="JURI" <?= ($_POST['role'] ?? '') === 'JURI' ? 'selected' : '' ?>>
                            ⭐ Juri - Penilai
                        </option>
                        <option value="PANITIA" <?= ($_POST['role'] ?? '') === 'PANITIA' ? 'selected' : '' ?>>
                            🛡️ Panitia - Admin
                        </option>
                    </select>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
                </button>
            </form>
            
            <div class="login-footer">
                <a href="/">
                    <i class="bi bi-arrow-left"></i> Kembali ke Homepage
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/js/main.js"></script>
</body>
</html>
