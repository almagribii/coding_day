<?php
include 'includes/db_config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? 0;
    $panitia_id = $_POST['panitia_id'] ?? 0; 

    if ($team_id > 0 && $panitia_id > 0) {
        try {
          
            $stmt = $pdo->prepare("
                UPDATE teams 
                SET is_verified = 1, verified_by_user_id = :panitia_id 
                WHERE id = :team_id AND is_verified = 0
            ");

            $stmt->execute([
                ':panitia_id' => $panitia_id,
                ':team_id' => $team_id
            ]);

            if ($stmt->rowCount() > 0) {
                header("Location: panitia_dashboard.php?success=verified");
                exit;
            } else {
                header("Location: panitia_dashboard.php?error=nochange");
                exit;
            }

        } catch (PDOException $e) {
            header("Location: panitia_dashboard.php?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}
?>