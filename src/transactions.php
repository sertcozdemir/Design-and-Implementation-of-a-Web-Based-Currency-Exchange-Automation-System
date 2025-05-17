<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];

// Filtre için gelen GET değerlerini alalım (boşsa default boş)
$transaction_type = isset($_GET['transaction_type']) ? trim($_GET['transaction_type']) : '';
$currency_code = isset($_GET['currency_code']) ? trim($_GET['currency_code']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Kullanıcının geçmiş işlem yaptığı döviz kodlarını çekelim (view değil doğrudan transactions tablosundan)
// Çünkü burada alias gerek ve currencies tablosuyla join yapıyoruz
$currencyOptionsStmt = $conn->prepare("
    SELECT DISTINCT c.currency_code 
    FROM transactions t
    JOIN currencies c ON t.currency_id = c.currency_id
    WHERE t.user_id = ?
");
$currencyOptionsStmt->bind_param("i", $user_id);
$currencyOptionsStmt->execute();
$currencyResult = $currencyOptionsStmt->get_result();

$currencyCodes = [];
while ($cur = $currencyResult->fetch_assoc()) {
    $currencyCodes[] = $cur['currency_code'];
}
$currencyOptionsStmt->close();

// İşlem türleri
$typeMap = [
    'buy' => 'Döviz Alım',
    'sell' => 'Döviz Satış',
    'deposit' => 'Bakiye Yükleme',
    'admin_deposit' => 'Admin Bakiye Yükleme',
    'admin_send' => 'Admin Para Gönderme',
];

// View’da alias yok, direkt sütun isimleri kullanılır
$sql = "SELECT * FROM user_transactions_view WHERE user_id = ?";

$params = [$user_id];
$param_types = "i";

if ($transaction_type !== '') {
    $sql .= " AND transaction_type = ?";
    $param_types .= "s";
    $params[] = $transaction_type;
}
if ($currency_code !== '') {
    $sql .= " AND currency_code = ?";
    $param_types .= "s";
    $params[] = $currency_code;
}
if ($start_date !== '') {
    $sql .= " AND transaction_time >= ?";
    $param_types .= "s";
    $params[] = $start_date . " 00:00:00";
}
if ($end_date !== '') {
    $sql .= " AND transaction_time <= ?";
    $param_types .= "s";
    $params[] = $end_date . " 23:59:59";
}

$sql .= " ORDER BY transaction_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>İşlem Geçmişi</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
<a href="dashboard.php"><button>Geri Dön</button></a>
<h2>İşlem Geçmişi - <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

<form method="GET" action="transactions.php">
    <label for="transaction_type">İşlem Türü:</label>
    <select id="transaction_type" name="transaction_type">
        <option value="">Tümü</option>
        <?php foreach ($typeMap as $key => $label): ?>
            <option value="<?php echo $key; ?>" <?php if ($transaction_type === $key) echo 'selected'; ?>>
                <?php echo $label; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="currency_code">Döviz Kodu:</label>
    <select id="currency_code" name="currency_code">
        <option value="">Tümü</option>
        <?php foreach ($currencyCodes as $code): ?>
            <option value="<?php echo htmlspecialchars($code); ?>" <?php if ($currency_code === $code) echo 'selected'; ?>>
                <?php echo htmlspecialchars($code); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="start_date">Başlangıç Tarihi:</label>
    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">

    <label for="end_date">Bitiş Tarihi:</label>
    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

    <button type="submit">Filtrele</button>
    <a href="transactions.php"><button type="button">Temizle</button></a>
</form>

<table>
    <tr>
        <th>İşlem Türü</th>
        <th>Döviz</th>
        <th>Anlık Kur</th>
        <th>Miktar</th>
        <th>İşlem Öncesi Bakiye (TL)</th>
        <th>İşlem Sonrası Bakiye (TL)</th>
        <th>Toplam Fiyat (TL)</th>
        <th>Tarih</th>
    </tr>

    <?php
    while ($row = $result->fetch_assoc()) {
        $label = $typeMap[$row['transaction_type']] ?? ucfirst($row['transaction_type']);
        $currency = $row['currency_code'] ?? 'TL';

        // Formatlama
        $rate = number_format($row['rate_at_time'], 6, ',', '.');
        $amount = number_format($row['amount'], 6, ',', '.');
        $balance_before = number_format($row['balance_before'], 6, ',', '.');
        $balance_after = number_format($row['balance_after'], 6, ',', '.');
        $price = number_format($row['total_price_try'], 6, ',', '.');
        $date = date("d.m.Y H:i", strtotime($row['transaction_time']));

        echo "<tr>
                <td>$label</td>
                <td>$currency</td>
                <td>$rate</td>
                <td>$amount</td>
                <td>$balance_before</td>
                <td>$balance_after</td>
                <td>$price</td>
                <td>$date</td>
              </tr>";
    }
    $stmt->close();
    ?>
</table>

<a href="dashboard.php"><button>Geri Dön</button></a>
</body>
</html>