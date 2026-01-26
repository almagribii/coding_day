<?php
include 'includes/db_config.php'; 
include 'includes/db_config.php'; 
include 'includes/mongo_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'] ?? 0;
    $jury_id = $_POST['jury_id'] ?? 0;
    $score = filter_var($_POST['score'], FILTER_VALIDATE_INT);
    $comments = $_POST['comments'] ?? '';

    if ($submission_id > 0 && $jury_id > 0 && $score !== false) {
        try {
            $stmt_check = $pdo->prepare("SELECT id FROM scores WHERE submission_id = ? AND jury_user_id = ?");
            $stmt_check->execute([$submission_id, $jury_id]);
            $existing_score = $stmt_check->fetch();

            if ($existing_score) {
                $stmt = $pdo->prepare("
                    UPDATE scores 
                    SET score = ?, comments = ?
                    WHERE id = ?
                ");
                $stmt->execute([$score, $comments, $existing_score['id']]);
                $message = "Nilai berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO scores (submission_id, jury_user_id, score, comments) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$submission_id, $jury_id, $score, $comments]);
                $message = "Nilai berhasil disimpan!";

                // --- MULAI KODE NOSQL (MONGODB) ---
                if ($logCollection) {
                    $logCollection->insertOne([
                        'event' => 'JURY_SCORING',
                        'jury_id' => (int)$jury_id,
                        'submission_id' => (int)$submission_id,
                        'nilai' => (int)$score,
                        'komentar' => $comments,
                        'waktu' => new MongoDB\BSON\UTCDateTime()
                    ]);
                }
                // --- SELESAI KODE NOSQL ---
            }

            $pdo->prepare("UPDATE submissions SET status = 'RATED' WHERE id = ?")->execute([$submission_id]);

            header("Location: juri_dashboard.php?msg=" . urlencode($message));
            exit;

        } catch (\PDOException $e) {
            header("Location: juri_dashboard.php?error=" . urlencode("Gagal menyimpan nilai: " . $e->getMessage()));
            exit;
        }
    } else {
        header("Location: juri_dashboard.php?error=" . urlencode("Data penilaian tidak valid."));
        exit;
    }
}
// Redirect default
header("Location: juri_dashboard.php");
exit;
?>