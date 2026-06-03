<?php
require_once "db.php";

if(!isset($_SESSION["user"])) { die("Login required"); }

$issueId = (int)$_POST["issueId"];
$rating  = (int)$_POST["rating"];
$raterId = (int)$_SESSION["user"]["id"];

if($issueId<=0 || $rating<1 || $rating>5) die("Invalid");

$ownerQ = $conn->query("SELECT user_id FROM issues WHERE id=$issueId");
if($ownerQ->num_rows==0) die("Issue not found");
$ownerId = (int)$ownerQ->fetch_assoc()["user_id"];

$check = $conn->query("SELECT id FROM issue_ratings WHERE issue_id=$issueId AND rater_id=$raterId");
if($check->num_rows>0) die("Already rated");

$conn->query("INSERT INTO issue_ratings(issue_id,rater_id,rating) VALUES($issueId,$raterId,$rating)")
  or die("Rating failed");

$conn->query("UPDATE users SET points = points + $rating WHERE id=$ownerId")
  or die("Points failed");

echo "OK";
?>