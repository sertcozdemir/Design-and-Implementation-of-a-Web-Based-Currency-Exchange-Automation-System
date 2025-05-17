<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'fetch_rates.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$code = strtoupper(trim($_POST['currency_code']));
$name = trim($_POST['currency_name']);

if (empty($code) || empty($name)) {
    echo "❌ Lütfen tüm alanları doldurun.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

// Aynı kod zaten varsa ekleme
$check = $conn->prepare("SELECT currency_id FROM currencies WHERE currency_code = ?");
$check->bind_param("s", $code);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "❌ Bu döviz zaten ekli.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

// Yeni döviz kodunu ekle
$insert = $conn->prepare("INSERT INTO currencies (currency_code, currency_name) VALUES (?, ?)");
$insert->bind_param("ss", $code, $name);
$insert->execute();

if ($insert->affected_rows === 0) {
    echo "❌ Döviz eklenirken hata oluştu.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

// Yeni eklenen currency_id’yi al
$currency_id = $insert->insert_id;

// API’den oranları çek
$apiKey = 'b490cc16f12d55d2065247e9';
$endpoint = "https://v6.exchangerate-api.com/v6/$apiKey/latest/TRY";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!$data || !isset($data['conversion_rates'][$code])) {
    echo "❌ API'de bu döviz kodu bulunamadı veya veri alınamadı.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

$rate = $data['conversion_rates'][$code];
$rate_to_try = round(1 / $rate, 6);
$buy_rate = round($rate_to_try * 1.001, 6);
$sell_rate = round($rate_to_try * 0.999, 6);

// exchange_rates tablosuna insert ya da update (varsa güncelle)
$stmt = $conn->prepare("
    INSERT INTO exchange_rates (currency_id, buy_rate, sell_rate, updated_at)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        buy_rate = VALUES(buy_rate),
        sell_rate = VALUES(sell_rate),
        updated_at = NOW()
");
$stmt->bind_param("idd", $currency_id, $buy_rate, $sell_rate);

if (!$stmt->execute()) {
    echo "❌ exchange_rates tablosuna ekleme/güncelleme hatası: " . $stmt->error . "<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

echo "✅ $code başarıyla eklendi.<br><a href='admin.php'><button>Geri Dön</button></a>";
?>