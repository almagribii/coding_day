<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

requireRole('PANITIA');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coding-day-app/panitia');
    exit;
}

$user = getCurrentUser();
$team_id = $_POST['team_id'] ?? '';
$admin_id = $user['id'];

// Validation
if (empty($team_id)) {
    $_SESSION['error'] = 'ID tim tidak valid!';
    header('Location: /coding-day-app/panitia');
    exit;
}

try {
    // Convert string team_id to MongoDB ObjectId if needed
    $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
    $adminObjectId = new MongoDB\BSON\ObjectId($admin_id);
    
    // Update team verification
    $result = $teamsCollection->updateOne(
        [
            '_id' => $teamObjectId,
            'is_verified' => false
        ],
        [
            '$set' => [
                'is_verified' => true,
                'verified_by_user_id' => $adminObjectId,
                'verified_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );

    if ($result->getModifiedCount() > 0) {
        // Log verification
        $verificationLogsCollection->insertOne([
            'team_id' => $teamObjectId,
            'admin_id' => $adminObjectId,
            'verified_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        $_SESSION['success'] = 'Tim berhasil diverifikasi!';
    } else {
        $_SESSION['error'] = 'Tim sudah terverifikasi atau tidak ditemukan.';
    }
    
    header("Location: /coding-day-app/panitia");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal memverifikasi tim: ' . $e->getMessage();
    header('Location: /coding-day-app/panitia');
    exit;
}
?>
