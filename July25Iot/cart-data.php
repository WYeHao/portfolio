<?php
$servername = "localhost";
$dbname = "finaltest";
$username = "root";
$password = "";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "SELECT id, productName, price, quantity FROM ScannedItems ORDER BY timestamp DESC";
$result = $conn->query($sql);

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
} else {
    echo "<tr><td colspan='6'>No scanned items found.</td></tr>";
}

$conn->close();
?>
