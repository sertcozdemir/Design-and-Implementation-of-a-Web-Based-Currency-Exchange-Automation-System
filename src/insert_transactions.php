<?php
function insert_transaction($conn, $user_id, $currency_id, $type, $amount, $total_price, $rate, $balance_before, $balance_after) {
    $stmt = $conn->prepare("INSERT INTO transactions 
        (user_id, currency_id, transaction_type, amount, total_price_try, rate_at_time, balance_before, balance_after)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iisddddd", 
        $user_id, 
        $currency_id, 
        $type, 
        $amount, 
        $total_price, 
        $rate, 
        $balance_before, 
        $balance_after
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}
?>