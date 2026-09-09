<?php
$servername = "localhost";
$dbname = "finaltest";
$username = "root";
$password = "";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    $method = $_GET['method'] ?? 'Unknown';

    // set random number
    $invoice_no = time();
    $method = $_GET['method'] ?? 'Unknown';

    $resultTotal = $conn->query("SELECT SUM(price*quantity) AS subtotal FROM ScannedItems");
    $rowTotal = $resultTotal->fetch_assoc();
    $grand_Total = $rowTotal['subtotal'] * 1.06;

    // insert to orders table
    $conn->query("
        INSERT INTO orders (invoice_no, uid, productName, price, quantity, total, grand_total, timestamp, payment_method)
        SELECT '$invoice_no', uid, productName, price, quantity, price*quantity, $grand_Total, NOW(), '$method'
        FROM ScannedItems
    ");

    // clear scannedItems table 
    $conn->query("DELETE FROM ScannedItems");

    // return checkout
    header("Location: /july25iot/check-out.php");
    exit();
}

// delete items
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmtDel = $conn->prepare("DELETE FROM ScannedItems WHERE id = ?");
    $stmtDel->bind_param("i", $delete_id);
    $stmtDel->execute();
    $stmtDel->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['update_id']) && isset($_POST['change'])) {
    $id = intval($_POST['update_id']);
    $delta = intval($_POST['change']);

    $stmt = $conn->prepare("UPDATE ScannedItems 
                            SET quantity = GREATEST(quantity + ?, 1) 
                            WHERE id=?");
    $stmt->bind_param("ii", $delta, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['uid'])) {
    $uid = $_POST['uid'];
    $stmt = $conn->prepare("SELECT productName, price FROM sensordata WHERE uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $stmt->bind_result($productName, $price);

    if ($stmt->fetch()) {
        $name = $productName;
        $p = $price;
    }
    $stmt->close();

    if (isset($name)) {
        $check = $conn->prepare("SELECT id, quantity FROM ScannedItems WHERE uid=?");
        $check->bind_param("s", $uid);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $newQty = $row['quantity'] + 1;

            $update = $conn->prepare("UPDATE ScannedItems SET quantity=?, timestamp=NOW() WHERE id=?");
            $update->bind_param("ii", $newQty, $row['id']);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare(
                "INSERT INTO ScannedItems (uid, productName, price, quantity, timestamp) VALUES (?, ?, ?, 1, NOW())"
            );
            $insert->bind_param("ssd", $uid, $name, $p);
            $insert->execute();
            $insert->close();
        }

        $check->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// clear cart
$clear = $_GET['clear'] ?? 0;
if ($clear) {
    $result = false;
} else {
    $sql = "SELECT id, productName, price, quantity, timestamp FROM ScannedItems ORDER BY timestamp DESC";
    $result = $conn->query($sql);
}
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
    background: url("/July25Iot/image/checkout.jpg") no-repeat center center fixed;
    background-size: cover;
}
:root {
    --primary-color: #1b1a1ac3;
    --accent-color: #ff8c00;
    --gradient: linear-gradient(90deg, #02ff56ff, #023a02ff);
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
    display: flex;
    justify-content: center;
    gap: 25px;
    padding: 20px;
}
.table-container {
    flex: 2;
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.summary-container {
    background: linear-gradient(135deg, #ffffff, #fafafa);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 300px;
    height: fit-content;
}
.summary-container h3 {
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    font-size: 20px;
}
.summary-container strong span {
    font-size: 20px;
    color: #e65100;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
}
thead th {
    background: #009688;
    color: white;
    padding: 14px;
    font-size: 15px;
}
tbody td {
    padding: 14px;
    text-align: center;
    border-bottom: 1px solid #0f0101ff;
}
tbody tr:hover {
    background: #f4f2f2ff;
}
th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #110101ff;
}
th {
    background-color: #01050fff;
    color: white;
}
.quantity {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.quantity button {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 18px;
    font-weight: bold;
    line-height: 1;
}
.quantity button:hover {
    background: #00796b;
}
.quantity input {
    width: 40px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 4px;
}
.checkout-btn {
    display: block;
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    text-align: center;
    background-color: #ff9800;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}
.checkout-btn:hover {
    background-color: #d88201ff;
}
.cart-summary-top {
    position: fixed;
    top: 20px;
    left: 20px;
    display: flex;
    align-items: center;
    background: #009688;
    color: white;
    padding: 10px 18px;
    border-radius: 30px;
    font-weight: bold;
    font-size: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.cart-summary-top span {
    background: red;
    padding: 4px 10px;
    border-radius: 50%;
    margin-left: 8px;
    font-size: 14px;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); background: red; }
    50% { transform: scale(1.2); background: #ff4d4d; }
    100% { transform: scale(1); background: red; }
}
</style>
<script>
function updateTotal() {
    let rows = document.querySelectorAll("tbody tr");
    let subtotal = 0;
    let totalItems = 0;
    rows.forEach(row => {
        let price = parseFloat(row.querySelector(".price").innerText.replace(/[^0-9.]/g, ""));
        let qty = parseInt(row.querySelector(".qty-input").value);
        let total = price * qty;
        row.querySelector(".row-total").innerText = "RM " + total.toFixed(2);
        subtotal += total;
        totalItems += qty;
    });
    let sst = subtotal * 0.06;
    let grandTotal = subtotal + sst;
    document.getElementById("subtotal").innerText = "RM " + subtotal.toFixed(2);
    document.getElementById("sst").innerText = "RM " + sst.toFixed(2);
    document.getElementById("grand-total").innerText = "RM " + grandTotal.toFixed(2);
    document.getElementById("item-count").innerText = totalItems;
}

function changeQty(btn, delta) {
    let input = btn.parentElement.querySelector(".qty-input");
    let value = parseInt(input.value) + delta;
    if (value < 1) value = 1;
    input.value = value;
    updateTotal();
}

window.onload = updateTotal;

function checkout() {
    let grandTotal = document.getElementById("grand-total").innerText.replace("RM ", "");
    window.location.href = "/july25iot/payment.php?total=" + encodeURIComponent(grandTotal);
}

async function fetchCart() {
    const res = await fetch('cart-data.php');
    const html = await res.text();
    document.querySelector('tbody').innerHTML = html;
    updateTotal();
}

// Refresh the shopping cart every 2 seconds
setInterval(fetchCart, 1000);
window.onload = fetchCart;

</script>
</head>
<body>
<div class="cart-summary-top">🛒 Items: <span id="item-count">0</span></div>
<h2>Shopping Cart</h2>
<div class="container">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                        <td>'.$i++.'</td>
                        <td>'.$row["productName"].'</td>
                        <td class="price">RM '.number_format($row["price"],2).'</td>
                        <td class="quantity">
                            <form method="post" style="display:flex;align-items:center;gap:6px;">
                                <input type="hidden" name="update_id" value="'.$row["id"].'">
                                <button type="submit" name="change" value="-1">-</button>
                                <input type="text" class="qty-input" value="'.$row["quantity"].'" readonly>
                                <button type="submit" name="change" value="1">+</button>
                            </form>
                        </td>
                        <td class="row-total">RM '.number_format($row["price"] * $row["quantity"],2).'</td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="'.$row["id"].'">
                                <button type="submit" style="cursor:pointer;color:red;font-size:18px;border:none;background:none;">🗑</button>
                            </form>
                        </td>
                    </tr>';
                }
                $result->free();
            } else {
                echo "<tr><td colspan='6'>No scanned items found.</td></tr>";
            }
            $conn->close();
            ?>
            </tbody>
        </table>
    </div>
    <div class="summary-container">
        <h3>Order Summary</h3>
        <p>Subtotal: <span id="subtotal">RM 0.00</span></p>
        <p>SST (6%): <span id="sst">RM 0.00</span></p>
        <p><strong>Grand Total: <span id="grand-total">RM 0.00</span></strong></p>
        <button class="checkout-btn" onclick="checkout()">Checkout</button>
    </div>
</div>

<?php if(isset($_GET['error']) && $_GET['error'] == 'invalid_total'): ?>
<div style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
            background: #ffcccc; color: #900; padding: 10px 20px;
            border-radius: 10px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
    ⚠️ Total amount is invalid. Please check your cart!
</div>
<?php endif; ?>

</body>
</html>
