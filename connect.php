<?php
ob_start();
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "borsa";

try {
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch (PDOException $pe) {
die ("Could not connect to the database $dbname :" . $pe->getMessage());
}

$Setting=$conn->query('SELECT * FROM settings')->fetch();
?>