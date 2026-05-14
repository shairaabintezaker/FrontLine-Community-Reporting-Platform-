<?php
require_once "db.php";

$full = trim($_POST["fullName"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$pass = $_POST["password"] ?? "";

if($full=="" || $username=="" || $email=="" || $pass==""){
  header("Location: ../register.html?err=Fill%20all%20required%20fields"); exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=? LIMIT 1");
$stmt->bind_param("ss",$email,$username);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows>0){
  header("Location: ../register.html?err=Email%20or%20Username%20already%20exists"); exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users(full_name,username,email,phone,password_hash) VALUES(?,?,?,?,?)");
$stmt->bind_param("sssss",$full,$username,$email,$phone,$hash);
$ok = $stmt->execute();

if($ok){
  header("Location: ../login.html?msg=registered"); exit;
}
header("Location: ../register.html?err=Registration%20failed"); exit;
?>
