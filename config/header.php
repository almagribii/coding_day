<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Coding Day 2026' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/coding-day-app/public/css/style.css">
    
    <?= $additionalCSS ?? '' ?>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-code-slash"></i>
                <span>CODING DAY 2026</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['role'])): ?>
                        <li class="nav-item">
                            <a href="leaderboard.php" class="nav-link">
                                <i class="bi bi-trophy"></i> Leaderboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="bi bi-person-circle"></i>
                                <?= htmlspecialchars($_SESSION['email'] ?? 'User') ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="badge bg-primary"><?= $_SESSION['role'] ?></span>
                        </li>
                        <li class="nav-item ms-3">
                            <a href="/coding-day-app/logout" class="btn btn-danger btn-sm">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
