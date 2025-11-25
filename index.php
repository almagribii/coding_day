<?php
function redirect_to_dashboard($role) {
    if ($role === 'PANITIA') {
        header('Location: panitia_dashboard.php');
    } elseif ($role === 'PESERTA') {
        header('Location: peserta_dashboard.php');
    } elseif ($role === 'JURI') {
        header('Location: juri_dashboard.php'); 
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_role = $_POST['role'] ?? 'PESERTA';
    redirect_to_dashboard($selected_role);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Sistem Manajemen Lomba Coding Day</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-box { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center; }
        select, button { padding: 10px; margin-top: 15px; width: 100%; box-sizing: border-box; }
        button { background-color: #3498db; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Selamat Datang di Aplikasi Lomba</h2>
        <p>Pilih peran Anda untuk simulasi login:</p>
        
        <form method="POST" action="index.php">
            <label for="role">Masuk Sebagai:</label>
            <select id="role" name="role">
                <option value="PESERTA">Peserta (Tim)</option>
                <option value="PANITIA">Panitia (Admin)</option>
                <option value="JURI">Juri (Penilai)</option>
            </select>
            <button type="submit">Masuk ke Dashboard</button>
        </form>
    </div>
</body>
</html>