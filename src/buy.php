<?php
session_start();
require_once 'db.php';
require_once 'insert_transactions.php';
require_once 'get_user_balance.php';
require_once 'auth.php';
check_login();

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

$balance_before = get_user_balance($conn, $user_id);
if ($balance_before === false) {
    echo "❌ Kullanıcı bulunamadı!<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

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