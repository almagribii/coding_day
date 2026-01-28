<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

requireRole('JURI');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coding-day-app/juri');
    exit;
}

$user = getCurrentUser();
$submission_id = $_POST['submission_id'] ?? '';
$jury_id = $user['id'];
$score = filter_var($_POST['score'] ?? null, FILTER_VALIDATE_INT);
$comments = trim($_POST['comments'] ?? '');

// Validation
if (empty($submission_id) || $score === false || $score < 0 || $score > 100) {
    $_SESSION['error'] = 'Data tidak valid! Nilai harus antara 0-100.';
    header('Location: /coding-day-app/juri');
    exit;
}

try {
    $submissionObjectId = new MongoDB\BSON\ObjectId($submission_id);
    $juryObjectId = new MongoDB\BSON\ObjectId($jury_id);
    
    // Check if score already exists
    $existing_score = $scoresCollection->findOne([
        'submission_id' => $submissionObjectId,
        'jury_user_id' => $juryObjectId
    ]);

    if ($existing_score) {
        // Update existing score
        $scoresCollection->updateOne(
            ['_id' => $existing_score['_id']],
            [
                '$set' => [
                    'score' => $score,
                    'comments' => $comments,
                    'rated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );
        $message = "Nilai berhasil diperbarui!";
    } else {
        // Insert new score
        $scoresCollection->insertOne([
            'submission_id' => $submissionObjectId,
            'jury_user_id' => $juryObjectId,
            'score' => $score,
            'comments' => $comments,
            'rated_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $message = "Nilai berhasil disimpan!";
    }
    
    // Update submission status
    $submissionsCollection->updateOne(
        ['_id' => $submissionObjectId],
        ['$set' => ['status' => 'RATED']]
    );
    
    // Log to activity logs
    $activityLogsCollection->insertOne([
        'event' => 'JURY_SCORING',
        'jury_id' => $juryObjectId,
        'submission_id' => $submissionObjectId,
        'score' => $score,
        'comments' => $comments,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
        'metadata' => [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]
    ]);
    
    $_SESSION['success'] = $message;
    header("Location: /coding-day-app/juri");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal menyimpan nilai: ' . $e->getMessage();
    header('Location: /coding-day-app/juri');
    exit;
}
?>
