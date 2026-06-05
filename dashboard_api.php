<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  echo json_encode(["ok" => false]);
  exit;
}

$uid = (int)$_SESSION["user"]["id"];

$points = 0;
$stmt = $conn->prepare("SELECT points FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) $points = (int)$row["points"];

$reports_week = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM service_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($res && $row = $res->fetch_assoc()) $reports_week = (int)$row["total"];

$open_reports = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM service_reports WHERE status = 'Open'");
if ($res && $row = $res->fetch_assoc()) $open_reports = (int)$row["total"];

$upcoming_events = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM events WHERE event_date >= CURDATE()");
if ($res && $row = $res->fetch_assoc()) $upcoming_events = (int)$row["total"];

$fund_total = 0;
$res = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM fund_donations");
if ($res && $row = $res->fetch_assoc()) $fund_total = (int)$row["total"];

$rank = 1;
$stmt = $conn->prepare("SELECT COUNT(*) + 1 AS rank_no FROM users WHERE points > ?");
$stmt->bind_param("i", $points);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) $rank = (int)$row["rank_no"];

$latest_event = null;
$res = $conn->query("SELECT e.title, e.event_date, e.event_time, e.location,
                            SUM(CASE WHEN er.response_type = 'Going' THEN 1 ELSE 0 END) AS going_count
                     FROM events e
                     LEFT JOIN event_responses er ON er.event_id = e.id
                     WHERE e.event_date >= CURDATE()
                     GROUP BY e.id
                     ORDER BY e.event_date ASC, e.event_time ASC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) $latest_event = $row;

echo json_encode([
  "ok" => true,
  "points" => $points,
  "rank" => $rank,
  "reports_week" => $reports_week,
  "open_reports" => $open_reports,
  "upcoming_events" => $upcoming_events,
  "fund_total" => $fund_total,
  "latest_event" => $latest_event
]);
?>
