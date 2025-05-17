<?php
session_start();
include 'db.php';
include 'insert_transactions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$currency_id = intval($_POST['currency_id']);
$amount = floatval($_POST['amount']);

// Satış kuru çekiliyor
$stmt = $conn->prepare("SELECT sell_rate FROM exchange_rates WHERE currency_id=?");
$stmt->bind_param("i", $currency_id);
$stmt->execute();
$result = $stmt->get_result();
$currency = $result->fetch_assoc();

if (!$currency) {
    echo "❌ Geçersiz para birimi!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

$sell_rate = $currency['sell_rate'];
$total_price = $amount * $sell_rate;

// Kullanıcının döviz cüzdanı kontrolü
$stmt = $conn->prepare("SELECT amount FROM wallets WHERE user_id=? AND currency_id=?");
$stmt->bind_param("ii", $user_id, $currency_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row || $row['amount'] < $amount) {
    echo "❌ Elinizde bu kadar {$amount} birim döviz yok!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

// TL bakiyesi (öncesi)
$stmt = $conn->prepare("SELECT balance FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$balance_before = $res->fetch_assoc()['balance'];

$balance_after = $balance_before + $total_price;

$conn->begin_transaction();
try {
    // ❌ TL bakiyesi güncellenmiyor
    // ❌ Döviz cüzdanı güncellenmiyor

    // ✅ Sadece işlem kaydı bırakılıyor
    insert_transaction($conn, $user_id, $currency_id, 'sell', $amount, $total_price, $sell_rate, $balance_before, $balance_after);
    
    $conn->commit();
    echo "✅ Satış başarılı!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Hata oluştu: " . $e->getMessage();
}
?>
