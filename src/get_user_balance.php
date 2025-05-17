<?php
function get_user_balance($conn, $user_id) {
    $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['balance'];
    }
    return 0;
}
?>