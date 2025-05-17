<?php
include 'db.php';

function fetchExchangeRates($conn) {
    $apiKey = 'b490cc16f12d55d2065247e9';
    $endpoint = "https://v6.exchangerate-api.com/v6/$apiKey/latest/TRY";

    // cURL ile daha güvenli veri çekme
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        error_log("API'den veri alınamadı: " . date('Y-m-d H:i:s'));
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || $data['result'] != 'success' || !isset($data['conversion_rates'])) {
        error_log("API yanıtı geçersiz: " . print_r($data, true));
        return false;
    }

    $rates = $data['conversion_rates'];

    // currencies tablosundaki para kodlarını çek
    $query = "SELECT currency_id, currency_code FROM currencies";
    $result = $conn->query($query);
    if (!$result) {
        error_log("Veritabanından para kodları alınamadı: " . $conn->error);
        return false;
    }

    // Hazırla: exchange_rates için
    $stmt = $conn->prepare("
        INSERT INTO exchange_rates (currency_id, buy_rate, sell_rate) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            buy_rate = VALUES(buy_rate), 
            sell_rate = VALUES(sell_rate),
            updated_at = NOW()
    ");

    // Hazırla: currency_rates_history için
    $stmtHistory = $conn->prepare("
        INSERT INTO currency_rates_history (currency_id, rate_buy, rate_sell, recorded_at) 
        VALUES (?, ?, ?, NOW())
    ");

    while ($row = $result->fetch_assoc()) {
        $currency_id = $row['currency_id'];
        $currency_code = $row['currency_code'];

        if (isset($rates[$currency_code]) && $rates[$currency_code] > 0) {
            $rate_to_try = (1 / $rates[$currency_code]);
            $buy_rate = ($rate_to_try * 1.001);  // %0.1 alış makası
            $sell_rate = ($rate_to_try * 0.999); // %0.1 satış makası

            // exchange_rates güncelle
            $stmt->bind_param("idd", $currency_id, $buy_rate, $sell_rate);
            $stmt->execute();

            // currency_rates_history kaydet
            $stmtHistory->bind_param("idd", $currency_id, $buy_rate, $sell_rate);
            $stmtHistory->execute();
        }
    }

    // Güncelleme zamanını kaydet
    $conn->query("UPDATE currency_update_log SET last_updated = NOW() WHERE id = 1");

    return true;
}
?>