<?php
session_start();
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css?v=1">

    <title>Giriş Yap</title>
</head>
<body>
    <h2 style = "text-align : center;">Giriş Yap</h2>
    <form action="login.php" method="POST">
        Kullanıcı Adı: <input type="text" name="username" required><br><br>
        Şifre: <input type="password" name="password" required><br><br>
        <input type="submit" value="Giriş Yap">
    </form>
</body>
<a href="register_form.php" style= "text-align: center; display: block;">Hesabın yok mu? Kayıt ol</a>
<a href="forgot_password.php" style= "text-align: center; display: block; padding-top:10px;">Şifremi unuttum</a>
</html>

<?php if ($error): ?>
    <div style="text-align: center; color: red; font-weight: bold;"><?php echo $error; ?></div><br>
<?php endif; ?>
