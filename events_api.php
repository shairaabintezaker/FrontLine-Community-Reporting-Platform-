<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  http_response_code(401);
  echo json_encode([]);
  exit;
}

$type = trim($_GET["type"] ?? "All");
$limit = max(1, min(50, (int)($_GET["limit"] ?? 12)));
$uid = (int)$_SESSION["user"]["id"];
$allowed = ["All", "Upcoming", "Volunteer", "Awareness", "Environment", "Meetup"];
if (!in_array($type, $allowed, true)) $type = "All";

$sql = "SELECT e.id, e.title, e.type, e.event_date, e.event_time, e.location, e.details, e.created_at,
               u.full_name AS organizer_name,
               SUM(CASE WHEN er.response_type = 'Going' THEN 1 ELSE 0 END) AS going_count,
               SUM(CASE WHEN er.response_type = 'Interested' THEN 1 ELSE 0 END) AS interested_count,
               (SELECT er2.response_type FROM event_responses er2 WHERE er2.event_id = e.id AND er2.user_id = ? LIMIT 1) AS my_response
        FROM events e
        JOIN users u ON u.id = e.organizer_id
        LEFT JOIN event_responses er ON er.event_id = e.id
        WHERE e.event_date >= CURDATE()";

if ($type !== "All") {
  $sql .= " AND e.type = ?";
}
$sql .= " GROUP BY e.id ORDER BY e.event_date ASC, e.event_time ASC LIMIT ?";

$stmt = $conn->prepare($sql);
if ($type !== "All") {
  $stmt->bind_param("isi", $uid, $type, $limit);
} else {
  $stmt->bind_param("ii", $uid, $limit);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) {
  $row["going_count"] = (int)($row["going_count"] ?? 0);
  $row["interested_count"] = (int)($row["interested_count"] ?? 0);
  $rows[] = $row;
}
echo json_encode($rows);
?>
