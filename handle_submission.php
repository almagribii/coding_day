<?php
include 'includes/db_config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? 0;
    $gdrive_link = $_POST['gdrive_link'] ?? '';

    if ($team_id > 0 && !empty($gdrive_link)) {
        try {
            $stmt_check = $pdo->prepare("SELECT id FROM submissions WHERE team_id = ?");
            $stmt_check->execute([$team_id]);
            $existing_submission = $stmt_check->fetch();

            if ($existing_submission) {
                $stmt = $pdo->prepare("
                    UPDATE submissions 
                    SET gdrive_link = ?, submitted_at = NOW(), status = 'SENT'
                    WHERE id = ?
                ");
                $stmt->execute([$gdrive_link, $existing_submission['id']]);
                $message = "Submission berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO submissions (team_id, gdrive_link) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$team_id, $gdrive_link]);
                $message = "Submission baru berhasil dikirim!";
            }

            header("Location: peserta_dashboard.php?msg=" . urlencode($message));
            exit;

        } catch (\PDOException $e) {
            header("Location: peserta_dashboard.php?error=" . urlencode("Gagal saat mengirim submission: " . $e->getMessage()));
            exit;
        }
    } else {
        header("Location: peserta_dashboard.php?error=" . urlencode("Data tidak lengkap."));
        exit;
    }
}
header("Location: peserta_dashboard.php");
exit;
?>