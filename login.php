<?php
require_once "db.php";

$user = trim($_POST["user"] ?? "");
$pass = $_POST["password"] ?? "";

$stmt = $conn->prepare("SELECT id,full_name,username,email,password_hash,points FROM users WHERE email=? OR username=? LIMIT 1");
$stmt->bind_param("ss",$user,$user);
$stmt->execute();
$res = $stmt->get_result();

if(!$row = $res->fetch_assoc()){
  header("Location: ../login.html?err=Invalid%20credentials"); exit;
}
if(!password_verify($pass, $row["password_hash"])){
  header("Location: ../login.html?err=Invalid%20credentials"); exit;
}

$_SESSION["user"] = [
  "id" => (int)$row["id"],
  "name" => $row["full_name"],
  "username" => $row["username"],
  "points" => (int)$row["points"]
];

header("Location: ../home.html"); exit;
?>
