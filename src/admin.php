<!DOCTYPE html>
<html>
<head>
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
<?php
session_start();
require_once 'db.php';

require_once 'auth.php';
check_login();

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT is_admin FROM users WHERE user_id = $user_id");
$row = $res->fetch_assoc();
if ($row['is_admin'] != 1) {
    echo "❌ Bu sayfaya erişim izniniz yok.";
    exit;
}

// Filtreleme işlemleri
$where_wallet = "WHERE 1";
if (isset($_GET['wallet_username']) && $_GET['wallet_username'] !== '') {
    $wallet_username = $conn->real_escape_string($_GET['wallet_username']);
    $where_wallet .= " AND username = '$wallet_username'";
}
if (isset($_GET['wallet_currency']) && $_GET['wallet_currency'] !== '') {
    $wallet_currency = $conn->real_escape_string($_GET['wallet_currency']);
    $where_wallet .= " AND currency_code = '$wallet_currency'";
}
$wallets = $conn->query("SELECT * FROM user_wallet_view $where_wallet");

$where_log = "WHERE 1";
if (isset($_GET['log_username']) && $_GET['log_username'] !== '') {
    $log_username = $conn->real_escape_string($_GET['log_username']);
    $where_log .= " AND username = '$log_username'";
}
if (isset($_GET['log_currency']) && $_GET['log_currency'] !== '') {
    $log_currency = $conn->real_escape_string($_GET['log_currency']);
    $where_log .= " AND currency_code = '$log_currency'";
}
if (isset($_GET['log_type']) && $_GET['log_type'] !== '') {
    $log_type = $conn->real_escape_string($_GET['log_type']);
    $where_log .= " AND transaction_type = '$log_type'";
}
if (isset($_GET['log_date']) && $_GET['log_date'] !== '') {
    $log_date = $conn->real_escape_string($_GET['log_date']);
    $where_log .= " AND DATE(transaction_time) = '$log_date'";
}
$logs = $conn->query("SELECT * FROM user_transaction_log_view $where_log");
?>

<h2>Admin Paneli</h2>
<a href="dashboard.php"><button>Kullanıcı Paneline Dön</button></a>
<button id="toggleLogsBtn" style="margin-top:20px;">Kullanıcı İşlem Geçmişini Göster</button>
<div id="logsTable" style="display:<?= (isset($_GET['log_filter']) ? 'block' : 'none') ?>; margin-top: 10px;">
    <h3>İşlem Geçmişi (Loglar)</h3>
    <form method="GET">
        <input type="hidden" name="log_filter" value="1">
        Kullanıcı:
        <select name="log_username">
            <option value="">Tümü</option>
            <?php $users = $conn->query("SELECT username FROM users"); while ($u = $users->fetch_assoc()) {
                $selected = ($_GET['log_username'] ?? '') == $u['username'] ? 'selected' : '';
                echo "<option value='{$u['username']}' $selected>{$u['username']}</option>";
            } ?>
        </select>
        Döviz:
        <select name="log_currency">
            <option value="">Tümü</option>
            <?php $currencies = $conn->query("SELECT currency_code FROM currencies"); while ($c = $currencies->fetch_assoc()) {
                $selected = ($_GET['log_currency'] ?? '') == $c['currency_code'] ? 'selected' : '';
                echo "<option value='{$c['currency_code']}' $selected>{$c['currency_code']}</option>";
            } ?>
        </select>
        Tür:
        <select name="log_type">
            <option value="">Tümü</option>
            <option value="buy" <?= ($_GET['log_type'] ?? '') == 'buy' ? 'selected' : '' ?>>Alış</option>
            <option value="sell" <?= ($_GET['log_type'] ?? '') == 'sell' ? 'selected' : '' ?>>Satış</option>
        </select>
        Tarih:
        <input type="date" name="log_date" value="<?= $_GET['log_date'] ?? '' ?>">
        <input type="submit" value="Filtrele">
        <a href="admin_panel.php"><button type="button">Temizle</button></a>
    </form>
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
        <?php while ($row = $logs->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['transaction_id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['transaction_type']}</td>
                    <td>" . ($row['currency_code'] ?? 'TL') . "</td>
                    <td>{$row['currency_amount']}</td>
                    <td>" . number_format($row['total_price_try'], 6, ',', '.') . "</td>
                    <td>{$row['transaction_time']}</td>
                  </tr>";
        } ?>
    </table>
</div>

<button id="toggleWalletsBtn">Cüzdanları Göster</button>
<div id="walletsTable" style="display:<?= (isset($_GET['wallet_filter']) ? 'block' : 'none') ?>; margin-top: 10px;">
    <h3>Kullanıcı Cüzdanları</h3>
    <form method="GET">
        <input type="hidden" name="wallet_filter" value="1">
        Kullanıcı:
        <select name="wallet_username">
            <option value="">Tümü</option>
            <?php $users = $conn->query("SELECT username FROM users"); while ($u = $users->fetch_assoc()) {
                $selected = ($_GET['wallet_username'] ?? '') == $u['username'] ? 'selected' : '';
                echo "<option value='{$u['username']}' $selected>{$u['username']}</option>";
            } ?>
        </select>
        Döviz:
        <select name="wallet_currency">
            <option value="">Tümü</option>
            <?php $currencies = $conn->query("SELECT currency_code FROM currencies"); while ($c = $currencies->fetch_assoc()) {
                $selected = ($_GET['wallet_currency'] ?? '') == $c['currency_code'] ? 'selected' : '';
                echo "<option value='{$c['currency_code']}' $selected>{$c['currency_code']}</option>";
            } ?>
        </select>
        <input type="submit" value="Filtrele">
        <a href="admin_panel.php"><button type="button">Temizle</button></a>
    </form>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Cüzdan ID</th>
            <th>Kullanıcı</th>
            <th>TL Bakiye</th>
            <th>Döviz Kodu</th>
            <th>Döviz Adı</th>
            <th>Döviz Miktarı</th>
        </tr>
        <?php while ($row = $wallets->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['wallet_id']}</td>
                    <td>{$row['username']}</td>
                    <td>" . number_format($row['tl_balance'], 6, ',', '.') . "</td>
                    <td>" . ($row['currency_code'] ?? '-') . "</td>
                    <td>" . ($row['currency_name'] ?? '-') . "</td>
                    <td>" . ($row['currency_amount'] ?? 0) . "</td>
                  </tr>";
        } ?>
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
    <select name="user_id" required>
        <?php $users = $conn->query("SELECT user_id, username FROM users"); while ($user = $users->fetch_assoc()) {
            echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
        } ?>
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

