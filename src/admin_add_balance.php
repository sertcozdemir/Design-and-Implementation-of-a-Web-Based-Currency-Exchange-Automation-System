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

// Admin kontrolü
$admin_id = $_SESSION['user_id'];
$res = $conn->query("SELECT is_admin, balance FROM users WHERE user_id = $admin_id");
$row = $res->fetch_assoc();
if ($row['is_admin'] != 1) {
    echo "❌ Bu sayfaya erişim izniniz yok.";
    exit;
}
$admin_balance_before = $row['balance'];

// POST verileri
$target_user_id = intval($_POST['user_id']);
$amount = floatval($_POST['amount']);

if ($amount <= 0) {
    echo "❌ Geçersiz miktar girdiniz.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}
if ($admin_balance_before < $amount) {
    echo "❌ Yetersiz admin bakiyesi.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}
// Admin kendine para gönderemez kontrolü
if ($target_user_id === $admin_id) {
    echo "❌ Kendinize para gönderemezsiniz.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}

// Kullanıcının mevcut bakiyesi
$stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "❌ Kullanıcı bulunamadı.<br><a href='admin.php'><button>Geri Dön</button></a>";
    exit;
}
$user = $result->fetch_assoc();
$user_balance_before = $user['balance'];
$user_balance_after = $user_balance_before + $amount;

// Admin işlem kaydı (TL çıkışı)
$admin_balance_after = $admin_balance_before - $amount;

// Admin bakiyesini veritabanında güncelle
$stmt_admin_update = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ?");
$stmt_admin_update->bind_param("di", $admin_balance_after, $admin_id);
$stmt_admin_update->execute();
insert_transaction($conn, $admin_id, NULL, 'admin_send', -$amount, -$amount, NULL, $admin_balance_before, $admin_balance_after);

// Kullanıcı işlem kaydı (TL girişi)
insert_transaction($conn, $target_user_id, NULL, 'admin_deposit', $amount, $amount, NULL, $user_balance_before, $user_balance_after);

echo "✅ $amount TL başarıyla kullanıcıya aktarıldı ve admin bakiyesi düşürüldü!<br><a href='admin.php'><button>Geri Dön</button></a>";
?>