<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Tarih adaptörü: chartjs-adapter-date-fns -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body style="padding-left: 200px;">

<!-- Bakiye ve Yükleme Butonu -->
<div class="balance-container">
    <div class="balance-box">
        <?php
        $res = $conn->query("SELECT balance FROM users WHERE user_id = $user_id");
        $row = $res->fetch_assoc();
         echo "<p style = 'background-color : black; padding:8px; border-radius:8px; color:white;'><i class='fas fa-money-bill-wave'></i> Bakiye: " . number_format($row['balance'], 6, ',', '.') . " TL</p>";
        ?>
    </div>
    <a href="add_balance_form.php" class="balance-btn"><i class="fas fa-wallet"></i> Bakiye Yükle</a>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3 class="nav-title" style="margin-top: 1px;">Menü </h3>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Kurlar</a>
    <a href="#buy"><i class="fas fa-sync-alt"></i> Alış / Satış</a>
    <a href="wallets.php"><i class="fas fa-wallet"></i> Cüzdan</a>
    <a href="account.php"><i class="fas fa-user-circle"></i> Hesabım</a>
    <?php if ($_SESSION['username'] === 'admin') {
        echo '<a href="admin.php"><i class="fas fa-tools"></i> Admin Paneli</a>';
    } ?>
    <div class="bottom-buttons">
        <a href="transactions.php"><i class="fas fa-history"></i> İşlem Geçmişi</a>
        <a href="logout.php" style="margin-bottom: 30px;"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <h2 class="greeting">Hoş geldin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <?php
    // Otomatik kur güncelleme kontrolü
    $res = $conn->query("SELECT last_updated FROM currency_update_log WHERE id = 1");
    $row = $res->fetch_assoc();
    $last_update = strtotime($row['last_updated']);
    $now = time();
    if ($now - $last_update > 1800) {
        @file_get_contents("http://localhost/vtys2/update_rates.php");
    }
    ?>

    <h3>Güncel Kur Bilgileri</h3>
    <table border="1">
        <tr>
            <th>Döviz Kodu</th>
            <th>Adı</th>
            <th>Alış Fiyatı</th>
            <th>Satış Fiyatı</th>
        </tr>
        <?php
        $result = $conn->query("
            SELECT c.currency_id, c.currency_code, c.currency_name, e.buy_rate, e.sell_rate 
            FROM currencies c
            JOIN exchange_rates e ON c.currency_id = e.currency_id
        ");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['currency_code']}</td>
                    <td>{$row['currency_name']}</td>
                    <td>" . number_format($row['buy_rate'], 6, ',', '.') . " TL</td>
                    <td>" . number_format($row['sell_rate'], 6, ',', '.') . " TL</td>
                  </tr>";
        }

        $updateQuery = $conn->query("SELECT last_updated FROM currency_update_log ORDER BY last_updated DESC LIMIT 1");
        if ($updateQuery && $updateRow = $updateQuery->fetch_assoc()) {
            $formattedDate = date("d.m.Y H:i", strtotime($updateRow['last_updated']));
            echo "<caption style='caption-side: bottom; text-align: right; padding-top: 8px; font-style: italic;'>
                    Son Güncelleme: {$formattedDate}
                  </caption>";
        }
        ?>
    </table>

    <h3>Döviz Kuru Değişim Grafiği</h3>
<select id="currencySelect" style="margin-bottom: 10px; font-size:14px;">
    <?php
    $result = $conn->query("SELECT c.currency_id, c.currency_code FROM currencies c ORDER BY c.currency_code");
    while ($row = $result->fetch_assoc()) {
        $selected = ($row['currency_code'] === 'USD') ? 'selected' : '';
        echo "<option value='{$row['currency_id']}' $selected>{$row['currency_code']}</option>";
    }
    ?>
</select>
<canvas id="currencyChart" width="800" height="400"></canvas>
    <!-- Kurları Güncelle Butonu -->
    <a href="update_rates.php" class="update-rates-btn" ><i class="fas fa-sync-alt"></i> Kurları Güncelle</a>

    <h3 id="buy">Döviz Al</h3>
    <form action="buy.php" method="POST" class="currency-form">
        <div class="form-group">
            <label for="currency_id">Döviz Seç:</label>
            <select name="currency_id" required class="currency-select">
                <?php
                $result = $conn->query("
                    SELECT c.currency_id, c.currency_code, e.buy_rate
                    FROM currencies c
                    JOIN exchange_rates e ON c.currency_id = e.currency_id
                ");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['currency_id']}'>{$row['currency_code']} - Alış: " . number_format($row['buy_rate'], 6, ',', '.') . " TL</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Miktar:</label>
            <input type="number" name="amount" min="1" required class="amount-input">
        </div>

        <div class="form-group">
            <input type="submit" value="Döviz Al" class="submit-button">
        </div>
    </form>

    <h3 id="sell">Döviz Sat</h3>
<form action="sell.php" method="POST" class="currency-form">
    <div class="form-group">
        <label for="currency_id">Döviz Seç:</label>
        <select name="currency_id" required class="currency-select">
            <?php
            $result = $conn->query("
                SELECT c.currency_id, c.currency_code, e.sell_rate
                FROM currencies c
                JOIN exchange_rates e ON c.currency_id = e.currency_id
            ");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['currency_id']}'>{$row['currency_code']} - Satış: " . number_format($row['sell_rate'], 6, ',', '.') . " TL</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="amount">Miktar:</label>
        <input type="number" name="amount" min="1" required class="amount-input">
    </div>

    <div class="form-group">
        <input type="submit" value="Döviz Sat" class="submit-button">
    </div>
</form>
</div>

<script>
const ctx = document.getElementById('currencyChart').getContext('2d');
let currencyChart;

function fetchAndDrawChart(currency_id) {
    fetch('get_currency_history.php?currency_id=' + currency_id)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.date);
            const buyRates = data.map(item => parseFloat(item.rate_buy));
            const sellRates = data.map(item => parseFloat(item.rate_sell));

            if (currencyChart) {
                currencyChart.destroy();
            }

            currencyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Alış Kuru',
                            data: buyRates,
                            borderColor: 'blue',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            fill: false,
                            tension: 0.3,
                        },
                        {
                            label: 'Satış Kuru',
                            data: sellRates,
                            borderColor: 'red',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: false,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                parser: 'yyyy-MM-dd',
                                unit: 'day',
                                displayFormats: {
                                    day: 'MMM d'
                                }
                            }
                        },
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(6);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(6);
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Hata:', err);
        });
}

// Sayfa yüklendiğinde seçili currency'nin grafiğini göster
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('currencySelect');
    fetchAndDrawChart(select.value);

    // Döviz seçimi değiştiğinde grafiği güncelle
    select.addEventListener('change', () => {
        fetchAndDrawChart(select.value);
    });
});
</script>
</body>
</html>