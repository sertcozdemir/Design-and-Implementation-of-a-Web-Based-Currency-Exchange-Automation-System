<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcıyı tamamen sil (TRIGGER işlemleri otomatik yapacak)
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    session_destroy(); // Oturumu kapat
    echo "<script>alert('✅ Hesabınız kalıcı olarak silindi.'); window.location.href = 'login_form.php';</script>";
    exit;
} else {
    echo "<script>alert('❌ Hesap silme başarısız oldu. Lütfen tekrar deneyin.'); window.location.href = 'account.php';</script>";
    exit;
}
?>