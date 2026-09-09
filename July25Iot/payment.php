<?php
$servername = "localhost";
$dbname = "finaltest";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$total = $_GET['total'] ?? 0;

if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    $method = $_GET['method'] ?? 'Unknown';
    $total = floatval($_GET['total'] ?? 0);

    if ($total <= 0) {
        header("Location: /july25iot/check-out.php?error=invalid_total");
        exit();
    }

    // create random number invoice_no
    $result = $conn->query("SELECT MAX(order_id) AS max_id FROM orders");
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] + 1;
    $invoice_no = "ORD" . str_pad($next_id, 5, "0", STR_PAD_LEFT);

    $grand_Total = $total;

    $invoice_no_esc = $conn->real_escape_string($invoice_no);
    $method_esc = $conn->real_escape_string($method);

    // insert to orders table
    $sql = "
        INSERT INTO orders (invoice_no, uid, productName, price, quantity, total, grand_total, timestamp, payment_method)
        SELECT 
            '{$invoice_no_esc}', 
            uid, 
            productName, 
            price, 
            quantity, 
            price * quantity, 
            {$grand_Total}, 
            NOW(), 
            '{$method_esc}'
        FROM ScannedItems
    ";

    if (!$conn->query($sql)) {
        die("Insert failed: " . $conn->error);
    }

    // clear value
    $conn->query("DELETE FROM ScannedItems");

    // 通知 ESP32 清零
    @file_get_contents("http://192.168.0.198/reset?ok=1");

    header("Location: /july25iot/check-out.php?invoice=" . urlencode($invoice_no));
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { 
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 0;
    color: #333;
    background: url("/July25Iot/image/payment.jpeg") no-repeat center center fixed;
    background-size: cover;
}
:root {
    --primary-color: #1b1a1ad3;       
    --accent-color: #ff8c00;
    --gradient: linear-gradient(90deg, #02ff28ff, #078144ff);
}
h2 {
    text-align: center;
    margin: 30px 0;
    color: var(--primary-color);
    font-weight: 700;
    font-size: 2.2rem;
    position: relative;
    padding-bottom: 15px;
    letter-spacing: 1px;
}
h2::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: 0;
    transform: translateX(-50%);
    width: 300px;
    height: 4px;
    background: var(--gradient);
    border-radius: 2px;
}
.container {
    max-width: 500px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.total-box {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    color: #d84315;
    margin-bottom: 30px;
}
.payment-method {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.payment-option {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fafafa;
    padding: 14px 18px;
    border-radius: 10px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: 0.3s;
}
.payment-option:hover {
    border-color: #4caf50;
    background: #f1f8e9;
}
.payment-option input {
    transform: scale(1.3);
}
.pay-btn, .back-btn {
    display: block;
    width: 100%;
    margin-top: 15px;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.pay-btn {
    background: #43a047;
    color: white;
}
.pay-btn:hover {
    background: #2e7d32;
}
.back-btn {
    background: #ccc;
    color: #333;
}
.back-btn:hover {
    background: #999;
}
</style>
<script>
function confirmPayment() {
    let payment = document.querySelector('input[name="payment"]:checked');
    if (!payment) {
        alert("⚠️ Please select a payment method!");
        return;
    }
    let amount = "<?php echo htmlspecialchars($total); ?>";
    alert("✅ You selected " + payment.value + " | Total: RM " + amount);

    let method = encodeURIComponent(payment.value);
    window.location.href = "/july25iot/payment.php?confirm=1&method=" + method + "&total=" + amount;
}
</script>
</head>
<body>

<h2>Select Payment Method</h2>

<div class="container">
    <div class="total-box">Total Amount: RM <?php echo htmlspecialchars($total); ?></div>

    <div class="payment-method">
        <label class="payment-option">
            <input type="radio" name="payment" value="Credit Card">
            💳 Credit Card
        </label>
        <label class="payment-option">
            <input type="radio" name="payment" value="Touch 'n Go eWallet">
            📱 Touch 'n Go eWallet
        </label>
        <label class="payment-option">
            <input type="radio" name="payment" value="GrabPay">
            🛵 GrabPay
        </label>
        <label class="payment-option">
            <input type="radio" name="payment" value="Cash">
            💵 Cash
        </label>
    </div>

    <button class="pay-btn" onclick="confirmPayment()">Confirm Payment</button>

    <form method="get" action="/july25iot/check-out.php">
        <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">
        <button type="submit" class="back-btn">Back to Checkout</button>
    </form>
</div>

</body>
</html>
