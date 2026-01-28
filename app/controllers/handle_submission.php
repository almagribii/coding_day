<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

requireRole('PESERTA');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coding-day-app/peserta');
    exit;
}

$user = getCurrentUser();
$team_id = $_POST['team_id'] ?? '';
$gdrive_link = trim($_POST['gdrive_link'] ?? '');

// Validation
if (empty($team_id) || empty($gdrive_link)) {
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
    $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
    $userObjectId = new MongoDB\BSON\ObjectId($user['id']);
    
    // Verify team ownership
    $team = $teamsCollection->findOne([
        '_id' => $teamObjectId,
        'leader_id' => $userObjectId,
        'is_verified' => true
    ]);
    
    if (!$team) {
        $_SESSION['error'] = 'Tim tidak valid atau belum terverifikasi!';
        header('Location: /coding-day-app/peserta');
        exit;
    }
    
    // Check for existing submission
    $existing_submission = $submissionsCollection->findOne([
        'team_id' => $teamObjectId
    ]);

    if ($existing_submission) {
        // Update existing
        $submissionsCollection->updateOne(
            ['_id' => $existing_submission['_id']],
            [
                '$set' => [
                    'gdrive_link' => $gdrive_link,
                    'submitted_at' => new MongoDB\BSON\UTCDateTime(),
                    'status' => 'READY_TO_RATE'
                ]
            ]
        );
        $message = "Submission berhasil diperbarui!";
    } else {
        // Insert new
        $submissionsCollection->insertOne([
            'team_id' => $teamObjectId,
            'gdrive_link' => $gdrive_link,
            'submitted_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'READY_TO_RATE'
        ]);
        $message = "Submission baru berhasil dikirim!";
    }
    
    // Log to MongoDB
    $activityLogsCollection->insertOne([
        'event' => 'SUBMISSION_SENT',
        'team_id' => $teamObjectId,
        'user_id' => $userObjectId,
        'link' => $gdrive_link,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
        'metadata' => [
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]
    ]);
    
    $_SESSION['success'] = $message;
    header("Location: /coding-day-app/peserta");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal menyimpan submission: ' . $e->getMessage();
    header('Location: /coding-day-app/peserta');
    exit;
}
?>
