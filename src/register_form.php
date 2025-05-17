
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css?v=1">

    <title>Kayıt Ol</title>
</head>
<body>
    <h2 style = "text-align : center;">Yeni Hesap Oluştur</h2>
    <form action="register.php" method="POST">
        Kullanıcı Adı: <input type="text" name="username" required><br><br>
        Şifre: <input type="password" name="password" required><br><br>
        E-posta: <input type="email" name="email" required><br><br>
        Telefon: <input type="text" name="phone" pattern="^\d{11}$" maxlength = "11" title="11 haneli telefon numarası girin" required><br><br>
        <input type="submit" value="Kayıt Ol">
    </form>

    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red; text-align:center'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']); // Bir kere göster, sonra temizle
    }
    ?>

    <br>
    <a href="login_form.php" style = "display: block; text-align: center;">Zaten hesabın var mı? Giriş yap</a>
</body>
</html>
