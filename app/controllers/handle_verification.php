<?php
require_once 'includes/auth.php';
require_once 'includes/db_config.php';

requireRole('PANITIA');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panitia_dashboard.php');
    exit;
}

$user = getCurrentUser();
$team_id = filter_var($_POST['team_id'] ?? 0, FILTER_VALIDATE_INT);
$admin_id = $user['id'];

// Validation
if (!$team_id) {
    $_SESSION['error'] = 'ID tim tidak valid!';
    header('Location: panitia_dashboard.php');
    exit;
}

try {
    // Update team verification
    $stmt = $pdo->prepare("
        UPDATE teams 
        SET is_verified = 1, verified_by_user_id = ? 
        WHERE id = ? AND is_verified = 0
    ");
    
    $stmt->execute([$admin_id, $team_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Tim berhasil diverifikasi!';
    } else {
        $_SESSION['error'] = 'Tim sudah terverifikasi atau tidak ditemukan.';
    }
    
    header("Location: panitia_dashboard.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal memverifikasi tim: ' . $e->getMessage();
    header('Location: panitia_dashboard.php');
    exit;
}
?>
