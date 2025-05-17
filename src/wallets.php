<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Cüzdanlar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
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

<h2 id="wallet" style = "text-align:center; font-size: 24px;">Döviz Cüzdanları</h3>
    <div class="wallet-grid">
    <?php
    $result = $conn->query("
        SELECT w.wallet_id, w.amount, c.currency_code, c.currency_name, e.sell_rate
        FROM wallets w
        JOIN currencies c ON w.currency_id = c.currency_id
        JOIN exchange_rates e ON c.currency_id = e.currency_id
        WHERE w.user_id = $user_id
    ");

    while ($row = $result->fetch_assoc()) {
        $wallet_id = $row['wallet_id'];
        $currency_name = $row['currency_name'];
        $currency_code = $row['currency_code'];
        $amount = $row['amount'];
        $sell_rate = $row['sell_rate'];
        $try_value = round($amount * $sell_rate, 2);

        echo "
        <div class='wallet-card' onclick=\"toggleDetails('details-$wallet_id')\">
            <i class='fas fa-wallet fa-2x'></i>
            <div class='currency-label'>{$currency_name} ({$currency_code})</div>
            <div class='wallet-details' id='details-$wallet_id'>
                <p><strong>Miktar:</strong> {$amount}</p>
                <p><strong>TL Değeri(Satış):</strong> " . number_format($try_value, 2, ',', '.') . " TL</p>
            </div>
        </div>
        ";
    }
    ?>
    </div>

    <script>
function toggleDetails(id) {
    const el = document.getElementById(id);
    el.style.display = (el.style.display === "none" || el.style.display === "") ? "block" : "none";
}
</script>

</body>
</html>