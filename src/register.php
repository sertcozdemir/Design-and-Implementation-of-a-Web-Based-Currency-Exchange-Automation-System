<?php
session_start();
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$phone = $_POST['phone'];

if (!preg_match('/^\d{11}$/', $phone)) {
    $_SESSION['error'] = "❌ Telefon numarası 11 haneli olmalıdır.";
    header("Location: register_form.php");
    exit;
}

$balance = 1000.00;

// Kullanıcı adı kontrolü
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "❌ Bu kullanıcı adı zaten alınmış.";
    header("Location: register_form.php");
    exit;
}

// Email kontrolü
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "❌ Bu e-posta adresi zaten kullanılıyor.";
    header("Location: register_form.php");
    exit;
}

// Kayıt
$stmt = $conn->prepare("INSERT INTO users (username, password, balance, email, phone)
                        VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdss", $username, $password, $balance, $email, $phone);
$stmt->execute();

$_SESSION['user_id'] = $conn->insert_id;
$_SESSION['username'] = $username;

header("Location: dashboard.php");
?>