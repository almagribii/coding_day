<?php
require_once '../../config/auth.php';
require_once '../../config/db_config.php';
require_once '../../config/mongo_config.php';

requireRole('PESERTA');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coding-day-app/peserta');
    exit;
}

$user = getCurrentUser();
$team_id = filter_var($_POST['team_id'] ?? 0, FILTER_VALIDATE_INT);
$gdrive_link = trim($_POST['gdrive_link'] ?? '');

// Validation
if (!$team_id || empty($gdrive_link)) {
    $_SESSION['error'] = 'Data tidak lengkap!';
    header('Location: /coding-day-app/peserta');
    exit;
}

// Validate Google Drive link
if (!preg_match('/^https:\/\/(drive|docs)\.google\.com\/.+/', $gdrive_link)) {
    $_SESSION['error'] = 'Link harus dari Google Drive!';
    header('Location: /coding-day-app/peserta');
    exit;
}

try {
    // Verify team ownership
    $stmt_verify = $pdo->prepare("SELECT id FROM teams WHERE id = ? AND leader_id = ? AND is_verified = 1");
    $stmt_verify->execute([$team_id, $user['id']]);
    
    if (!$stmt_verify->fetch()) {
        $_SESSION['error'] = 'Tim tidak valid atau belum terverifikasi!';
        header('Location: /coding-day-app/peserta');
        exit;
    }
    
    // Check for existing submission
    $stmt_check = $pdo->prepare("SELECT id FROM submissions WHERE team_id = ?");
    $stmt_check->execute([$team_id]);
    $existing_submission = $stmt_check->fetch();

    if ($existing_submission) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE submissions 
            SET gdrive_link = ?, submitted_at = NOW(), status = 'READY_TO_RATE'
            WHERE id = ?
        ");
        $stmt->execute([$gdrive_link, $existing_submission['id']]);
        $message = "Submission berhasil diperbarui!";
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO submissions (team_id, gdrive_link, status) 
            VALUES (?, ?, 'READY_TO_RATE')
        ");
        $stmt->execute([$team_id, $gdrive_link]);
        $message = "Submission baru berhasil dikirim!";
    }
    
    // Log to MongoDB if available
    if (isset($logCollection)) {
        try {
            $logCollection->insertOne([
                'event' => 'SUBMISSION_SENT',
                'team_id' => $team_id,
                'user_id' => $user['id'],
                'link' => $gdrive_link,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the request
            error_log("MongoDB logging failed: " . $e->getMessage());
        }
    }
    
    $_SESSION['success'] = $message;
    header("Location: /coding-day-app/peserta");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menyimpan submission: ' . $e->getMessage();
    header('Location: /coding-day-app/peserta');
    exit;
}
?>
