<?php
// login
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /july25iot/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #c0bfe9ff;
    background: url("/July25Iot/image/index.jpg") no-repeat center center fixed;
    background-size: cover;
}
:root {
    --primary-color: #333;       
    --accent-color: #ff8c00;
    --gradient: linear-gradient(90deg, #622f09ff, #f6b828ff);
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
.table-container {
    border: 2px solid transparent;
    background: #dfd9adff;
    background-clip: padding-box;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
form {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 25px;
}
form input {
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    width: 220px;
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}
form button {
    padding: 10px 20px;
    background: linear-gradient(45deg,#f5ac03,#ff8c00);
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s;
}
form button:hover {
    background: linear-gradient(45deg,#ff8c00,#f5ac03);
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    border-radius: 12px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #686726ff;
    color: white;
    text-transform: uppercase;
}
tr:hover {
    background-color: #f1f1f1;
    transition: 0.2s;
}
.checkout-btn {
    display: inline-block;
    width: 250px;
    margin: 10px 8px;
    padding: 15px;
    background: linear-gradient(45deg,#ff5722,#ff8a50);
    color: white;
    font-size: 18px;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
    font-weight: bold;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    animation: pulse 1.5s infinite;
}
.checkout-btn:hover {
    background: linear-gradient(45deg,#ff8a50,#ff5722);
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
.delete-btn {
    padding: 6px 14px;
    background: #f44336;
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}
.delete-btn:hover {
    background: #5c0101ff;
    transform: scale(1.05);
}
.top-links {
    text-align: center;
    margin-bottom: 20px;
    background: rgba(75, 80, 21, 0.85);
    padding: 12px 0;
    border-radius: 8px;
}

.top-links a {
    display: inline-block;
    margin: 0 20px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    text-decoration: none;
    padding: 6px 12px;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}
.top-links a:hover {
    color: var(--accent-color);
    border-bottom: 2px solid var(--accent-color);
}
</style>
</head>
<body>

<h2>Add Items</h2>

<?php
$servername = "localhost";
$dbname = "finaltest";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $uid = $_POST['uid'];
    $productName = $_POST['productName'];
    $price = $_POST['price'];

    $insert = "INSERT INTO SensorData (uid, productName, price, timestamp) 
               VALUES ('$uid', '$productName', '$price', NOW())";
    if ($conn->query($insert)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p style='text-align:center;color:red;'>Error: " . $conn->error . "</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $editId = $_POST['edit_id'];
    $uid = $_POST['uid'];
    $productName = $_POST['productName'];
    $price = $_POST['price'];

    $update = "UPDATE SensorData 
               SET uid='$uid', productName='$productName', price='$price'
               WHERE id='$editId'";
    if ($conn->query($update)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p style='text-align:center;color:red;'>Update Error: " . $conn->error . "</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $deleteId = $_POST['delete_id'];
    $delete = "DELETE FROM SensorData WHERE id='$deleteId'";
    if ($conn->query($delete)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p style='text-align:center;color:red;'>Delete Error: " . $conn->error . "</p>";
    }
}
?>
<div class="table-container">
    <div class="top-links">
        <a href="/july25iot/check-out.php">Check Customer Item</a>
        <a href="/july25iot/chart.php">View Charts</a>
        <a href="/july25iot/logout.php">Logout</a>
    </div>

    <form method="POST">
        <input type="text" name="uid" placeholder="Card UID" required>
        <input type="text" name="productName" placeholder="Product Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price (RM)" required>
        <button type="submit" name="add">Add Item</button>
    </form>
</div>

<div class="table-container">
<?php
$sql = "SELECT id, uid, productName, price, timestamp 
        FROM SensorData 
        ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<table>
          <tr>
            <th>#</th>
            <th>Card UID</th>
            <th>Product</th>
            <th>Price (RM)</th>
            <th>Scan Time</th>
            <th>Action</th>
          </tr>';
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $counter . '</td>
                <td>' . $row["uid"] . '</td>
                <td>' . $row["productName"] . '</td>
                <td>RM' . number_format($row["price"], 2) . '</td>
                <td>' . $row["timestamp"] . '</td>
                <td>

                    <!-- Edit 按钮 -->
                    <button type="button" class="edit-btn" style="background:#2196F3;color:white;border:none;padding:6px 14px;border-radius:20px;cursor:pointer;">Edit</button>

                    <form method="POST" class="edit-form" style="display:none;">
                        <input type="hidden" name="edit_id" value="' . $row["id"] . '">
                        <input type="text" name="uid" value="' . $row["uid"] . '" required>
                        <input type="text" name="productName" value="' . $row["productName"] . '" required>
                        <input type="number" step="0.01" name="price" value="' . $row["price"] . '" required>
                        <button type="submit" name="edit" style="background:#4CAF50;color:white;border:none;padding:6px 14px;border-radius:20px;cursor:pointer;">Save</button>
                        <button type="button" class="cancel-btn" style="margin-left:5px;padding:6px 14px;border-radius:20px;cursor:pointer;">Cancel</button>
                    </form>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="' . $row["id"] . '">
                        <button type="submit" name="delete" class="delete-btn">Delete</button>
                    </form>

                </td>
              </tr>';
        $counter++;
    }
    echo '</table>';
} else {
    echo "<p style='text-align:center;color:red;'>No scanned items yet.</p>";
}

$conn->close();
?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".edit-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            const td = btn.closest("td");
            td.querySelector(".edit-form").style.display = "inline";
            btn.style.display = "none";
            td.closest("tr").querySelector(".uid").style.display = "none";
            td.closest("tr").querySelector(".productName").style.display = "none";
            td.closest("tr").querySelector(".price").style.display = "none";
        });
    });

    document.querySelectorAll(".cancel-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            const td = btn.closest("td");
            td.querySelector(".edit-form").style.display = "none";
            td.querySelector(".edit-btn").style.display = "inline";
            td.closest("tr").querySelector(".uid").style.display = "table-cell";
            td.closest("tr").querySelector(".productName").style.display = "table-cell";
            td.closest("tr").querySelector(".price").style.display = "table-cell";
        });
    });
});
</script>

</body>
</html>
