<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının kayıtlı kartlarını çek
$stmt = $conn->prepare("SELECT * FROM credit_cards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards_result = $stmt->get_result();
$cards = [];
while ($row = $cards_result->fetch_assoc()) {
    $cards[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bakiye Yükle</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>

<h2>Bakiye Yükle</h2>
<label for="saved_card">💳 Kayıtlı Kart Seç:</label>
<select style="font-size:16px;" id="saved_card" onchange="fillCardInfo(this.value)">
    <option value="">-- Kart Seçin --</option>
    <?php foreach ($cards as $card): ?>
        <option value="<?= htmlspecialchars(json_encode($card)) ?>">
            <?= substr($card['card_number'], 0, 4) ?> **** **** <?= substr($card['card_number'], -4) ?> - <?= $card['card_holder_name'] ?>
        </option>
    <?php endforeach; ?>
</select>
<form action="add_balance.php" method="POST">
    <label>Yüklenecek Miktar (TL):</label>
    <input type="number" name="amount" step="0.01" min="1" required><br><br>

    <label>Kart Numarası:</label>
    <input type="text" name="card_number" id= "card_number"
       pattern="\d{15,16}" 
       title="Geçerli bir kart numarası giriniz." 
       maxlength="16" 
       required><br><br>

    <label>İsim Soyisim:</label>
    <input type="text" id = "card_name" name="card_name" required><br><br>

    <label>CVV:</label>
<input type="text" name="cvv"
       pattern="\d{3}"
       title="3 haneli sadece rakamlardan oluşan CVV girin"
       maxlength="3"
       required><br><br>


    <label>Son Kullanma Tarihi (MM/YY):</label>
    <input type="text" name="expiry" id="expiry" maxlength="5" placeholder="MM/YY" required><br><br>

    <div class="save-card-container" style="justify-content:left;">
        <label for="save_card" style = "padding-right: 5px; font-size:16px;">Bu kartı kaydet</label>
        <input type="checkbox" name="save_card" id="save_card" value="yes" style= "margin:5px;">
    </div>
    <input type="submit" value="Yükle">
</form>

<br><a href="dashboard.php"><button>Geri Dön</button></a>

<script>
document.getElementById('expiry').addEventListener('input', function (e) {
    let value = e.target.value.replace(/[^0-9]/g, ''); // Sadece rakamlar kalsın
    if (value.length >= 2 && !value.includes('/')) {
        value = value.slice(0, 2) + '/' + value.slice(2);
    }
    e.target.value = value.slice(0, 5); // Maksimum 5 karakter
});
</script>

<script>
function fillCardInfo(cardJson) {
    if (!cardJson) return;

    const card = JSON.parse(cardJson);
    const paddedMonth = card.expiry_month.toString().padStart(2, '0');
    document.getElementById('card_number').value = card.card_number;
    document.getElementById('card_name').value = card.card_holder_name;
    document.getElementById('expiry').value = paddedMonth + '/' + card.expiry_year;
}
</script>

</body>
</html>

