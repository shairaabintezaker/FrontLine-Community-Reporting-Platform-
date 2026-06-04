<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  http_response_code(401);
  echo json_encode([]);
  exit;
}

$service = trim($_GET["service"] ?? "");
$status = trim($_GET["status"] ?? "");
$allowed_status = ["Open", "In Progress", "Resolved"];

$sql = "SELECT sr.id, sr.service, sr.title, sr.type, sr.location, sr.details,
               sr.status, sr.created_at, u.full_name,
               ROUND(IFNULL(AVG(rf.rating),0),1) AS avg_rating,
               COUNT(rf.id) AS feedback_count
        FROM service_reports sr
        JOIN users u ON u.id = sr.user_id
        LEFT JOIN report_feedback rf ON rf.report_id = sr.id";

$where = [];
$params = [];
$types = "";

if ($service !== "" && $service !== "All") {
  $where[] = "sr.service = ?";
  $params[] = $service;
  $types .= "s";
}
if ($status !== "" && $status !== "All" && in_array($status, $allowed_status, true)) {
  $where[] = "sr.status = ?";
  $params[] = $status;
  $types .= "s";
}
if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " GROUP BY sr.id ORDER BY sr.id DESC LIMIT 120";

$stmt = $conn->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  $r["avg_rating"] = (float)$r["avg_rating"];
  $r["feedback_count"] = (int)$r["feedback_count"];
  $rows[] = $r;
}

echo json_encode($rows);
?>
