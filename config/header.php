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
    <link rel="stylesheet" href="/public/css/style.css">
    
    <?= $additionalCSS ?? '' ?>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-modern" style="background: linear-gradient(135deg, rgba(13, 17, 23, 0.98) 0%, rgba(22, 27, 34, 0.98) 100%) !important; backdrop-filter: blur(10px); border-bottom: 1px solid #30363d; padding: 0.75rem 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);">
        <div class="container-fluid">
            <a class="navbar-brand brand-enhanced" href="coding-day-app" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0;">
                <div class="brand-icon-wrapper" style="width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, #0969da 0%, #8957e5 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);">
                    <i class="bi bi-code-slash" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div class="brand-text" style="display: flex; flex-direction: column; line-height: 1.2;">
                    <span class="brand-title" style="font-weight: 800; font-size: 1.2rem; color: #e6edf3; letter-spacing: 0.5px;">CODING DAY</span>
                    <span class="brand-year" style="font-size: 0.75rem; font-weight: 600; color: #58a6ff; letter-spacing: 2px;">2026</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['role'])): ?>
                        <li class="nav-item">
                            <a href="coding-day-app/leaderboard" class="nav-link nav-link-enhanced" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem !important; background: rgba(88, 166, 255, 0.05); border: 1px solid transparent; border-radius: 8px; transition: all 0.3s ease;">
                                <i class="bi bi-trophy-fill" style="font-size: 1.1rem;"></i>
                                <span>Leaderboard</span>
                            </a>
                        </li>
                        
                        <!-- User Menu Dropdown -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle user-menu" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false"
                               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 1rem !important; background: rgba(22, 27, 34, 0.8); border: 1px solid #30363d; border-radius: 50px; transition: all 0.3s ease;">
                                <div class="user-avatar" style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #0969da 0%, #8957e5 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person-circle" style="font-size: 1.3rem; color: white;"></i>
                                </div>
                                <div class="user-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.15rem;">
                                    <span class="user-email" style="font-size: 0.875rem; font-weight: 600; color: #e6edf3; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($_SESSION['email'] ?? 'User') ?></span>
                                    <span class="user-role-badge badge-<?= strtolower($_SESSION['role']) ?>" style="font-size: 0.65rem; padding: 0.15rem 0.5rem; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; <?php 
                                        if($_SESSION['role'] == 'PESERTA') echo 'background: linear-gradient(135deg, #0969da 0%, #3771c8 100%);';
                                        elseif($_SESSION['role'] == 'JURI') echo 'background: linear-gradient(135deg, #8957e5 0%, #6e40c9 100%);';
                                        elseif($_SESSION['role'] == 'PANITIA') echo 'background: linear-gradient(135deg, #1f6f38 0%, #238636 100%);';
                                    ?> color: white;">
                                        <?= $_SESSION['role'] ?>
                                    </span>
                                </div>
                                <i class="bi bi-chevron-down ms-2" style="color: #8b949e;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown" style="background: #161b22; border: 1px solid #30363d; border-radius: 12px; padding: 0.5rem; min-width: 280px; margin-top: 0.5rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);">
                                <li class="dropdown-header" style="padding: 1rem; background: transparent;">
                                    <div class="dropdown-user-info" style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div class="dropdown-user-avatar" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #0969da 0%, #8957e5 100%); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-person-circle" style="font-size: 1.5rem; color: white;"></i>
                                        </div>
                                        <div>
                                            <div class="dropdown-user-name" style="font-size: 0.95rem; font-weight: 600; color: #e6edf3; margin-bottom: 0.2rem;"><?= htmlspecialchars($_SESSION['email'] ?? 'User') ?></div>
                                            <div class="dropdown-user-role" style="font-size: 0.8rem; color: #8b949e; text-transform: capitalize;"><?= $_SESSION['role'] ?></div>
                                        </div>
                                    </div>
                                </li>
                           
                                <li><hr class="dropdown-divider" style="border-color: #30363d; margin: 0.5rem 0;"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/logout" style="color: #da3633 !important; padding: 0.75rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s ease; font-size: 0.9rem;">
                                        <i class="bi bi-box-arrow-right" style="font-size: 1.1rem; width: 20px;"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
