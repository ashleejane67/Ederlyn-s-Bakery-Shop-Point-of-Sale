<?php
$host = "localhost";
$username = "root";
$password = ""; 
$database = "pos_bakery";


$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>