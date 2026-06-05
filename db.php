<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "frontline_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("DB Error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
