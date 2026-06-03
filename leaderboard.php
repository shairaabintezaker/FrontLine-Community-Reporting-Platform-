<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  echo json_encode(["ok" => false, "leaders" => [], "currentRank" => null]);
  exit;
}

$res = $conn->query("SELECT id, full_name, username, points FROM users ORDER BY points DESC, id ASC LIMIT 20");
$rows = [];
while ($r = $res->fetch_assoc()) {
  $rows[] = $r;
}

$uid = (int)$_SESSION["user"]["id"];
$stmt = $conn->prepare("SELECT points FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$currentRank = null;
$currentPoints = 0;
if ($row) {
  $currentPoints = (int)$row["points"];
  $rankStmt = $conn->prepare("SELECT COUNT(*) + 1 AS rank_no FROM users WHERE points > ?");
  $rankStmt->bind_param("i", $currentPoints);
  $rankStmt->execute();
  $currentRank = (int)$rankStmt->get_result()->fetch_assoc()["rank_no"];
}

echo json_encode([
  "ok" => true,
  "leaders" => $rows,
  "currentRank" => $currentRank,
  "currentPoints" => $currentPoints
]);
?>
