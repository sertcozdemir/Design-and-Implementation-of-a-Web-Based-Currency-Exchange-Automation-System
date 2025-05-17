<?php
session_start();
include 'db.php';
include 'insert_transactions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$currency_id = intval($_POST['currency_id']);
$amount = floatval($_POST['amount']);

// Kur bilgisi exchange_rates tablosundan
$stmt = $conn->prepare("SELECT buy_rate FROM exchange_rates WHERE currency_id=?");
$stmt->bind_param("i", $currency_id);
$stmt->execute();
$result = $stmt->get_result();
$currency = $result->fetch_assoc();

if (!$currency) {
    echo "❌ Geçersiz para birimi!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

$buy_rate = $currency['buy_rate'];
$total_price = $amount * $buy_rate;

// Kullanıcının mevcut bakiyesi
$stmt = $conn->prepare("SELECT balance FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    echo "❌ Kullanıcı bulunamadı!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

$balance_before = $user['balance'];

if ($balance_before < $total_price) {
    echo "❌ Yetersiz bakiye!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

$balance_after = $balance_before - $total_price;

$conn->begin_transaction();
try {
    // ❌ Bakiyeden düşme işlemi kaldırıldı
    // ❌ Wallet güncelleme işlemi kaldırıldı

    // ✅ Sadece işlem kaydı bırakılıyor, trigger hallediyor
    insert_transaction($conn, $user_id, $currency_id, 'buy', $amount, $total_price, $buy_rate, $balance_before, $balance_after);

    $conn->commit();
    echo "✅ Satın alma başarılı!<br><a href='dashboard.php'><button>Geri Dön</button></a>";

} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Hata oluştu: " . $e->getMessage();
}
?>