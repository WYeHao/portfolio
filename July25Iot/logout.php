<?php
session_start();
session_destroy();
header("Location: /july25iot/login.php");
exit();
?>
