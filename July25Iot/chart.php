<?php
$servername = "localhost";
$dbname = "finaltest";
$username = "root";
$password = "";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){
    die("Connection Fail: ".$conn->connect_error);
}

// Orders 
$sql = "
SELECT invoice_no, MAX(timestamp) AS last_time, MAX(grand_total) AS total
FROM orders
GROUP BY invoice_no
ORDER BY last_time DESC
LIMIT 40
";
$result = $conn->query($sql);


if (!$result) {
    die("SQL Error: " . $conn->error);
}

$orders = [];
while($row = $result->fetch_assoc()){
    $orders[] = $row;
}

$order_time  = json_encode(array_reverse(array_column($orders, 'last_time')));
$invoice_no  = json_encode(array_reverse(array_column($orders, 'invoice_no')));
$grand_total = json_encode(array_map('floatval', array_reverse(array_column($orders, 'total'))));

// Orders product
$sql2 = "SELECT productName, SUM(quantity) AS total_qty FROM orders GROUP BY productName";
$result2 = $conn->query($sql2);

$product_count = [];
while($row = $result2->fetch_assoc()){
    $product_count[$row['productName']] = intval($row['total_qty']);
}

$product_names = json_encode(array_keys($product_count));
$product_qty   = json_encode(array_values($product_count));

$result->free();
$conn->close();
?>
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.highcharts.com/highcharts.js"></script>
<style>
body {
    min-width: 310px;
    max-width: 1280px;
    margin: 0 auto;
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
    color: #333;
    padding: 20px;
    background: url("/July25Iot/image/chart.jpg") no-repeat center center fixed;
    background-size: cover;
}
h2 {
    font-size: 2.2rem;
    text-align: center;
    margin-bottom: 40px;
    color: #eaeaeaff;
    font-weight: bold;
    letter-spacing: 1px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}
.dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}
.container {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.container:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.18);
}
</style>
<body>
    <h2>Orders Records</h2>

    <div id="chart-product" class="container"></div>

    <div id="chart-uid" class="container"></div>

    <script>
        var order_time   = <?php echo $order_time; ?>;
        var invoice_no   = <?php echo $invoice_no; ?>;
        var grand_total  = <?php echo $grand_total; ?>;

        var product_names = <?php echo $product_names; ?>;
        var product_qty   = <?php echo $product_qty; ?>;

        Highcharts.chart('chart-product', {
            chart: { type: 'column' },
            title: { text: 'Product Sales Count' },
            xAxis: { categories: product_names, title: { text: 'Product' } },
            yAxis: { title: { text: 'Quantity Sold' }, allowDecimals: false },
            series: [{
                name: 'Quantity',
                data: product_qty,
                color: '#ff6600'
            }],
            credits: { enabled: false }
        });

        Highcharts.chart('chart-uid', {
            chart: { type: 'line' },
            title: { text: 'Orders Over Time' },
            xAxis: { categories: order_time, title: { text: 'Order Time' } },
            yAxis: { title: { text: 'Grand Total (RM)' } },
            series: [{
                name: 'Grand Total',
                data: grand_total
            }],
            credits: { enabled: false }
        });
    </script>
</body>
</html>
