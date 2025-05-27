<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "u727297653_gad";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
