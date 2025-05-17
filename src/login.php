<?php
session_start();
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if ($password == $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['login_error'] = "❌ Hatalı şifre!";
        header("Location: login_form.php");
        exit;
    }
} else {
    $_SESSION['login_error'] = "❌ Kullanıcı bulunamadı!";
    header("Location: login_form.php");
    exit;
}
?>