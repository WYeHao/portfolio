<?php
$conn = new mysqli("localhost", "root", "", "finaltest");
if ($conn->connect_error) die("DB error");

$result = $conn->query("SELECT SUM(price*quantity) AS subtotal FROM ScannedItems");
$row = $result->fetch_assoc();
$grandTotal = $row['subtotal'] * 1.06; // SST 6%
echo $grandTotal;
?>
