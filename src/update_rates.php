<?php
require_once 'db.php';
require_once 'fetch_rates.php';

if (fetchExchangeRates($conn)) {
    header("Location: dashboard.php?success=1");
} else {
    header("Location: dashboard.php?error=1");
}
exit;
?>