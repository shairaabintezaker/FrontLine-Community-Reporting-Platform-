<?php
require_once "db.php";

if (!isset($_SESSION["user"])) { http_response_code(401); echo "Login required"; exit; }

$uid = (int)($_SESSION["user"]["id"] ?? 0);
$amount = (int)($_POST["amount"] ?? 0);
$purpose = trim($_POST["purpose"] ?? "");
$note = trim($_POST["note"] ?? "");

if ($uid <= 0) { http_response_code(401); echo "Login required"; exit; }
if ($amount < 10) { http_response_code(400); echo "Minimum ৳10"; exit; }
if ($purpose === "") { http_response_code(400); echo "Purpose required"; exit; }

$stmt = $conn->prepare("INSERT INTO fund_donations(user_id, amount, purpose, note) VALUES(?, ?, ?, ?)");
$stmt->bind_param("iiss", $uid, $amount, $purpose, $note);

echo $stmt->execute() ? "OK" : "Failed";
?>
