<?php
require_once '../../config/auth.php';
require_once '../../config/db_config.php';
require_once '../../config/mongo_config.php';

requireRole('JURI');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coding-day-app/juri');
    exit;
}

$user = getCurrentUser();
$submission_id = filter_var($_POST['submission_id'] ?? 0, FILTER_VALIDATE_INT);
$jury_id = $user['id'];
$score = filter_var($_POST['score'] ?? null, FILTER_VALIDATE_INT);
$comments = trim($_POST['comments'] ?? '');

// Validation
if (!$submission_id || $score === false || $score < 0 || $score > 100) {
    $_SESSION['error'] = 'Data tidak valid! Nilai harus antara 0-100.';
    header('Location: /coding-day-app/juri');
    exit;
}

try {
    // Check if score already exists
    $stmt_check = $pdo->prepare("SELECT id FROM scores WHERE submission_id = ? AND jury_user_id = ?");
    $stmt_check->execute([$submission_id, $jury_id]);
    $existing_score = $stmt_check->fetch();

    if ($existing_score) {
        // Update existing score
        $stmt = $pdo->prepare("
            UPDATE scores 
            SET score = ?, comments = ?, rated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$score, $comments, $existing_score['id']]);
        $message = "Nilai berhasil diperbarui!";
    } else {
        // Insert new score
        $stmt = $pdo->prepare("
            INSERT INTO scores (submission_id, jury_user_id, score, comments) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$submission_id, $jury_id, $score, $comments]);
        $message = "Nilai berhasil disimpan!";
    }
    
    // Update submission status
    $pdo->prepare("UPDATE submissions SET status = 'RATED' WHERE id = ?")
        ->execute([$submission_id]);
    
    // Log to MongoDB if available
    if (isset($logCollection)) {
        try {
            $logCollection->insertOne([
                'event' => 'JURY_SCORING',
                'jury_id' => $jury_id,
                'submission_id' => $submission_id,
                'score' => $score,
                'comments' => $comments,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]
            ]);
        } catch (Exception $e) {
            error_log("MongoDB logging failed: " . $e->getMessage());
        }
    }
    
    $_SESSION['success'] = $message;
    header("Location: /coding-day-app/juri");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menyimpan nilai: ' . $e->getMessage();
    header('Location: /coding-day-app/juri');
    exit;
}
?>
