<?php
require_once "db.php";
if(!isset($_SESSION["user"])) { http_response_code(401); echo "Login required"; exit; }

$title = trim($_POST["title"] ?? "");
$category = trim($_POST["category"] ?? "");
$location = trim($_POST["location"] ?? "");
$desc = trim($_POST["description"] ?? "");

if($title=="" || $category=="" || $location==""){
  http_response_code(400); echo "Missing fields"; exit;
}

$uid = $_SESSION["user"]["id"];
$stmt = $conn->prepare("INSERT INTO issues(user_id,title,category,location,description) VALUES(?,?,?,?,?)");
$stmt->bind_param("issss",$uid,$title,$category,$location,$desc);
echo $stmt->execute() ? "OK" : "Failed";
?>