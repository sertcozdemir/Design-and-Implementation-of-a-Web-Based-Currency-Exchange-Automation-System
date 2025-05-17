<?php
session_start();
include 'db.php';
include 'insert_transactions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$card_number = $_POST['card_number'];
$card_name = trim($_POST['card_name']);
$cvv = $_POST['cvv'];
$expiry = $_POST['expiry'];
$save_card = isset($_POST['save_card']) && $_POST['save_card'] === 'yes';

// Kart doğrulama fonksiyonu (Luhn algoritması ve kart sağlayıcı kontrolü)
function validateCard($card_number) {
    $card_number = preg_replace('/\D/', '', $card_number); // Sadece rakamlar
    $length = strlen($card_number);

    $sum = 0;
    $sum1 = 0;
    $counter = 1;
    $first_two_digits = 0;
    $remain = 0;

    $temp_number = $card_number;

    while ($temp_number > 0) {
        $remain = $temp_number % 10;
        $temp_number = (int)($temp_number / 10);

        if ($counter % 2 == 0) {
            $doubled = $remain * 2;
            if ($doubled < 10) {
                $sum += $doubled;
            } else {
                $sum += (int)($doubled / 10) + ($doubled % 10);
            }
        } else {
            $sum1 += $remain;
        }

        if ($temp_number < 100 && $temp_number > 9) {
            $first_two_digits = $temp_number;
        }

        $counter++;
    }

    $sum_total = $sum + $sum1;

    if ($sum_total % 10 !== 0) {
        return false; // Luhn hatası
    }

    $first_digit = (int)substr($card_number, 0, 1);

    if ($first_digit == 4 && ($length == 13 || $length == 16)) {
        return true; // VISA
    } elseif ($first_two_digits >= 51 && $first_two_digits <= 55 && $length == 16) {
        return true; // MASTERCARD
    } elseif (($first_two_digits == 34 || $first_two_digits == 37) && $length == 15) {
        return true; // AMEX
    }

    return false; // Desteklenmeyen kart
}

// Doğrulamalar
if (!is_numeric($amount) || $amount <= 0) {
    echo "❌ Geçersiz miktar girdiniz.<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}
if (!preg_match('/^\d{15,16}$/', $card_number)) {
    echo "❌ Kart numarası 15 veya 16 haneli ve sadece rakamlardan oluşmalıdır.<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}

if (!validateCard($card_number)) {
    echo "❌ Kart numarası geçersiz veya desteklenmeyen kart türü.<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}

if (strlen($card_name) < 3) {
    echo "❌ Kart üzerindeki isim geçersiz.<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}

if (!preg_match('/^\d{3}$/', $cvv)) {
    echo "❌ CVV yalnızca 3 haneli rakam olmalıdır.<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}

if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
    echo "❌ Son kullanma tarihi formatı geçersiz. (MM/YY olmalı)<br><a href='add_balance_form.php'><button>Geri Dön</button></a>";
    exit;
}

// Mevcut bakiye sorgula
$stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "❌ Kullanıcı bulunamadı.<br><a href='dashboard.php'><button>Geri Dön</button></a>";
    exit;
}

$balance_before = $user['balance'];
$balance_after = $balance_before + $amount;

// Bakiye güncelleme
$stmt1 = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ?");
$stmt1->bind_param("di", $balance_after, $user_id);
$stmt1->execute();

// İşlemi kayıt et
insert_transaction($conn, $user_id, NULL, 'deposit', $amount, $total_price, NULL, $balance_before, $balance_after);

if ($save_card) {
    // Son kullanma tarihini MM/YY'den ayır
    list($month, $year) = explode('/', $expiry);

    // Kart zaten kayıtlı mı kontrol et
    $checkCard = $conn->prepare("SELECT * FROM credit_cards WHERE user_id = ? AND card_number = ?");
    $checkCard->bind_param("is", $user_id, $card_number);
    $checkCard->execute();
    $existingCard = $checkCard->get_result()->fetch_assoc();

    if (!$existingCard) {
        // Kayıt yoksa yeni kartı ekle
        $stmt_card = $conn->prepare("INSERT INTO credit_cards (user_id, card_holder_name, card_number, expiry_month, expiry_year) VALUES (?, ?, ?, ?, ?)");
        $stmt_card->bind_param("issss", $user_id, $card_name, $card_number, $month, $year);
        $stmt_card->execute();
    } else {
        // Kart zaten kayıtlıysa kaydetme kutusu işaretli olsa bile kaydetme
        echo "<br>💳 Bu kart zaten kayıtlı.";
    }
}
echo "✅ $amount TL başarıyla yüklendi!";
if ($save_card && !$existingCard) {
    echo "<br>💳 Kartınız başarıyla kaydedildi.";
}
echo "<br><a href='dashboard.php'><button>Geri Dön</button></a>";
?>