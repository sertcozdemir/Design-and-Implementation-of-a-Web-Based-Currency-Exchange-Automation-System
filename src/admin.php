<!DOCTYPE html>
<html>
<head>
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
<?php
session_start();
include 'db.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

// Yetki kontrolü
$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT is_admin FROM users WHERE user_id = $user_id");
$row = $res->fetch_assoc();
if ($row['is_admin'] != 1) {
    echo "❌ Bu sayfaya erişim izniniz yok.";
    exit;
}

// Cüzdanlar için sorgu
$wallets = $conn->query("SELECT * FROM user_wallet_view");

// Loglar için sorgu
$logs = $conn->query("SELECT * FROM user_transaction_log_view");
?>

<h2>Admin Paneli</h2>
<a href="dashboard.php"><button>Kullanıcı Paneline Dön</button></a>
<!-- Logları Göster/Gizle Butonu -->
<button id="toggleLogsBtn" style="margin-top:20px;">Kullanıcı İşlem Geçmişini Göster</button>

<div id="logsTable" style="display:none; margin-top: 10px;">
    <h3>İşlem Geçmişi (Loglar)</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Kullanıcı</th>
            <th>İşlem Türü</th>
            <th>Döviz</th>
            <th>Miktar</th>
            <th>Tutar (TL)</th>
            <th>Tarih</th>
        </tr>
        <?php
        while ($row = $logs->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['transaction_id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['transaction_type']}</td>
                    <td>" . (isset($row['currency_code']) ? $row['currency_code'] : 'TL') . "</td>
                    <td>{$row['currency_amount']}</td>
                    <td>" . number_format($row['total_price_try'], 6, ',', '.') . "</td>
                    <td>{$row['transaction_time']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>


<!-- Cüzdanları Göster/Gizle Butonu -->
<button id="toggleWalletsBtn">Cüzdanları Göster</button>

<div id="walletsTable" style="display:none; margin-top: 10px;">
    <h3>Kullanıcı Cüzdanları</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Cüzdan ID</th>
            <th>Kullanıcı</th>
            <th>TL Bakiye</th>
            <th>Döviz Kodu</th>
            <th>Döviz Adı</th>
            <th>Döviz Miktarı</th>
        </tr>
        <?php
        while ($row = $wallets->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['wallet_id']}</td>
                    <td>{$row['username']}</td>
                    <<td>" . number_format($row['tl_balance'], 6, ',', '.') . "</td>
                    <td>" . (isset($row['currency_code']) ? $row['currency_code'] : '-') . "</td>
                    <td>" . (isset($row['currency_name']) ? $row['currency_name'] : '-') . "</td>
                    <td>" . (isset($row['currency_amount']) ? $row['currency_amount'] : 0) . "</td>
                  </tr>";
        }
        ?>
    </table>
</div>

<h3>Yeni Döviz Ekle</h3>
<form action="add_currency.php" method="POST">
    Kodu: <input type="text" name="currency_code" required><br>
    Adı: <input type="text" name="currency_name" required><br>
    <input type="submit" value="Ekle">
</form>




<h3>Kullanıcıya Bakiye Ekle</h3>
<form action="admin_add_balance.php" method="POST">
    <label for="user_id">Kullanıcı Seç:</label>
    <select name="user_id" class="currency-select" required>
        <?php
        $users = $conn->query("SELECT user_id, username FROM users");
        while ($user = $users->fetch_assoc()) {
            echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
        }
        ?>
    </select>

    <label for="amount">Eklenecek Miktar (TL):</label>
    <input type="number" step="0.01" name="amount" min="0.01" required>

    <input type="submit" value="Bakiye Ekle">
</form>

<script>
document.getElementById('toggleWalletsBtn').addEventListener('click', function() {
    const tableDiv = document.getElementById('walletsTable');
    if (tableDiv.style.display === 'none') {
        tableDiv.style.display = 'block';
        this.textContent = 'Cüzdanları Gizle';
    } else {
        tableDiv.style.display = 'none';
        this.textContent = 'Cüzdanları Göster';
    }
});

document.getElementById('toggleLogsBtn').addEventListener('click', function() {
    const tableDiv = document.getElementById('logsTable');
    if (tableDiv.style.display === 'none') {
        tableDiv.style.display = 'block';
        this.textContent = 'Kullanıcı İşlem Geçmişini Gizle';
    } else {
        tableDiv.style.display = 'none';
        this.textContent = 'Kullanıcı İşlem Geçmişini Göster';
    }
});
</script>

</body>
</html>