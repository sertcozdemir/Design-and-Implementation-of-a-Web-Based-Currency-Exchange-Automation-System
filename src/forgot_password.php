<!DOCTYPE html>
<html>
<head>
    <title>Şifremi Unuttum</title>
    <link rel="stylesheet" href="style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2 style= "text-align:center;">Şifre Sıfırlama</h2>

<p>Şifrenizi sıfırlamak için kayıt olduğunuz e-posta adresinizi girin:</p>

<form action="forgot_password.php" method="POST">
    <input type="email" name="email" placeholder="E-posta adresiniz" required><br><br>
    <input type="submit" value="Sıfırlama Linki Gönder">
</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<p style='color: green; text-align:center;'>E-posta adresinize bir şifre sıfırlama bağlantısı gönderildi</p>";
}
?>
<br><a href="login_form.php" style = "text-align:center; display:block;"><i class="fa fa-arrow-left"></i>Girişe Dön</a>


</body>
</html>