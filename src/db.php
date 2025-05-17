<?php
$host = "localhost";
$user = "root";
$pass = "webweb41"; // XAMPP'te genelde şifresiz
$db = "merhaba";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}
?>