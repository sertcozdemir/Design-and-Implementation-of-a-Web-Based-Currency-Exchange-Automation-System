<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $conn->query("SELECT username, email, phone, created_at FROM users WHERE user_id = $user_id");
$user = $query->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hesabım</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="padding-left: 200px;">

<!-- Bakiye ve Yükleme Butonu -->
<div class="balance-container">
    <div class="balance-box">
        <?php
        $res = $conn->query("SELECT balance FROM users WHERE user_id = $user_id");
        $row = $res->fetch_assoc();
        echo "<p style = 'background-color :black; padding:8px; border-radius:8px; color:white;'><i class='fas fa-money-bill-wave'></i> Bakiye: " . number_format($row['balance'], 2, ',', '.') . " TL</p>";
        ?>
    </div>
    <a href="add_balance_form.php" class="balance-btn"><i class="fas fa-wallet"></i> Bakiye Yükle</a>
</div>

    <!-- SIDEBAR -->
<div class="sidebar">
    <h3 class="nav-title" style="margin-top: 1px;">Menü</h3>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Kurlar</a>
    <a href="dashboard.php#buy"><i class="fas fa-sync-alt"></i> Alış / Satış</a>
    <a href="wallets.php"><i class="fas fa-wallet"></i> Cüzdan</a>
    <a href="account.php"><i class="fas fa-user-circle"></i> Hesabım</a>
    <?php if ($_SESSION['username'] === 'admin') {
        echo '<a href="admin.php"><i class="fas fa-tools"></i> Admin Paneli</a>';
    } ?>
    <div class="bottom-buttons">
        <a href="transactions.php"><i class="fas fa-history"></i> İşlem Geçmişi</a>
        <a href="logout.php" style="margin-bottom: 30px;"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>
</div>
    <div class="main-content">
        <h2><i class="fas fa-user-circle"></i> Hesap Bilgilerim</h2>
        <table class="account-info">
            <tr><th>Kullanıcı Adı:</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
            <tr><th>E-Posta:</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
            <tr><th>Telefon:</th><td><?= htmlspecialchars($user['phone']) ?></td></tr>
            <tr><th>Hesap Oluşturma Tarihi:</th><td><?= date("d.m.Y H:i", strtotime($user['created_at'])) ?></td></tr>
        </table>

        <div class="account-buttons" style="margin: 20px 10px;">
            <a href="password_change_form.php" class="button"><i class="fas fa-key"></i> Şifre Değiştir</a>
            <a href="logout.php" class="button"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
            <a href="delete_account.php" class="button delete" onclick="return confirm('⚠️ Hesabınız kalıcı olarak silinecektir. Emin misiniz?');"><i class="fas fa-user-slash"></i> Hesabı Sil</a>
            <a href="dashboard.php"><i class="fa fa-arrow-left"></i>Geri Dön</a>
        </div>
    </div>
</body>
</html>