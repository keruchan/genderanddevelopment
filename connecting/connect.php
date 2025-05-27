<?php
$servername = "localhost";
$username = "u727297653_gad";
$password = "GadLSPU#123";
$database = "u727297653_gad";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
