<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "finaltest";

// database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=MD5(?)");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $user;
        header("Location: /july25iot/index.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: url("/July25Iot/image/login2.jpeg") no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    display: flex;
    height: 100vh;
    justify-content: center;
    align-items: center;
    position: relative;
}
body::before {
    content: "";
    position: absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.5);
}
.login-box {
    position: relative;
    background: rgba(255, 255, 255, 0.95);
    padding: 40px 35px;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    width: 340px;
    text-align: center;
    animation: fadeIn 1s ease;
}
.login-box h2 {
    margin-bottom: 25px;
    font-size: 24px;
    font-weight: bold;
    color: #222;
    position: relative;
}
.login-box h2::after {
    content: "";
    position: absolute;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg,#f5ac03,#ff8c00);
    left: 50%;
    transform: translateX(-50%);
    bottom: -8px;
    border-radius: 2px;
}
.input-group {
    position: relative;
    margin: 15px 0;
}
.input-group input {
    width: 100%;
    padding: 12px 40px 12px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    box-sizing: border-box;
}
.login-box button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: linear-gradient(45deg,#f5ac03,#ff8c00);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.3s;
}
.login-box button:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(0,0,0,0.3);
}
.error {
    background: #ffdddd;
    color: #a10000;
    padding: 10px;
    border: 1px solid #e57373;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}
@keyframes fadeIn {
    from {opacity:0; transform: translateY(-20px);}
    to {opacity:1; transform: translateY(0);}
}
</style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
