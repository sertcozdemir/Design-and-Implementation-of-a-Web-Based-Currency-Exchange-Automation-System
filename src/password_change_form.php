<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Şifre Değiştir</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <div class="main-content">
        <h2 style = "text-align: center;"><i class="fas fa-key"></i> Şifre Değiştir</h2>
        <form action="password_change.php" method="POST" class="password-form">
            <label>Eski Şifre:</label>
            <input type="password" name="old_password" required><br><br>

            <label>Yeni Şifre:</label>
            <input type="password" name="new_password" required><br><br>

            <label>Yeni Şifre (Tekrar):</label>
            <input type="password" name="confirm_password" required><br><br>

            <button type="submit">Şifreyi Güncelle</button>
        </form>
    </div>
</body>
</html>