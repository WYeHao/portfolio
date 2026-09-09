<?php
$servername = "localhost";
$username   = "root";  
$password   = "";      
$dbname     = "finaltest";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = $_POST['uid'] ?? $_GET['uid'] ?? null;

if ($uid) {
    $sql = "SELECT productName, price FROM sensordata WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $stmt->bind_result($productName, $price);

    if ($stmt->fetch()) {
        $name = $productName;
        $p = $price;
    }
    $stmt->close();

    if (isset($name)) {
        $stmtInsert = $conn->prepare(
            "INSERT INTO scanneditems (uid, productName, price, quantity, timestamp) VALUES (?, ?, ?, 1, NOW())"
        );
        if (!$stmtInsert) die("Insert Prepare failed: " . $conn->error);
        $stmtInsert->bind_param("ssd", $uid, $name, $p);
        $stmtInsert->execute();
        $stmtInsert->close();

        $stmtTotal = $conn->prepare(
            "SELECT SUM(quantity * price) AS total FROM scanneditems WHERE uid = ?"
        );
        $stmtTotal->bind_param("s", $uid);
        $stmtTotal->execute();
        $stmtTotal->bind_result($totalPrice);
        $stmtTotal->fetch();
        $stmtTotal->close();

        echo "Product: " . $name . " | Price: RM" . $p . " | Total: RM" . $totalPrice;
    } else {
        echo "No product found for UID: " . $uid;
    }
} else {
    echo "UID not received!";
}

$conn->close();

