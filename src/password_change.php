<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Mevcut şifreyi al
$res = $conn->query("SELECT password FROM users WHERE user_id = $user_id");
$row = $res->fetch_assoc();

if ($old_password !== $row['password']) {
    echo "❌ Eski şifre yanlış.";
    exit;
}

if ($new_password !== $confirm_password) {
    echo "❌ Yeni şifreler eşleşmiyor.";
    exit;
}

// Şifreyi güncelle
$conn->query("UPDATE users SET password = '$new_password' WHERE user_id = $user_id");

// Oturumu sonlandır
session_destroy();

// Mesaj ve yönlendirme
echo "<p>✅ Şifreniz başarıyla güncellendi. Giriş sayfasına yönlendiriliyorsunuz...</p>";
echo "<script>
        setTimeout(function() {
            window.location.href = 'login_form.php';
        }, 3000);
      </script>";
?>