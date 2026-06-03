<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  echo json_encode(["ok" => false]);
  exit;
}

$uid = (int)$_SESSION["user"]["id"];
$stmt = $conn->prepare("SELECT id, full_name, username, points FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$points = (int)($user["points"] ?? 0);
$rank = 0;
if ($user) {
  $r = $conn->prepare("SELECT COUNT(*) + 1 AS rank_no FROM users WHERE points > ?");
  $r->bind_param("i", $points);
  $r->execute();
  $rank = (int)$r->get_result()->fetch_assoc()["rank_no"];
}

function count_for($conn, $sql, $uid) {
  $s = $conn->prepare($sql);
  $s->bind_param("i", $uid);
  $s->execute();
  $row = $s->get_result()->fetch_assoc();
  return (int)($row["total"] ?? 0);
}

$report_count = count_for($conn, "SELECT COUNT(*) AS total FROM service_reports WHERE user_id = ?", $uid);
$event_count = count_for($conn, "SELECT COUNT(*) AS total FROM events WHERE organizer_id = ?", $uid);
$going_count = count_for($conn, "SELECT COUNT(*) AS total FROM event_responses WHERE user_id = ? AND response_type = 'Going'", $uid);
$donation_count = count_for($conn, "SELECT COUNT(*) AS total FROM fund_donations WHERE user_id = ?", $uid);
$feedback_count = count_for($conn, "SELECT COUNT(*) AS total FROM report_feedback WHERE user_id = ?", $uid);

$level = "New Member";
if ($points >= 500) $level = "Community Leader";
elseif ($points >= 250) $level = "Active Helper";
elseif ($points >= 100) $level = "Trusted Contributor";
elseif ($points >= 30) $level = "Helper";

$badges = [];
if ($report_count >= 1) $badges[] = "First Reporter";
if ($report_count >= 5) $badges[] = "Issue Solver";
if ($event_count >= 1) $badges[] = "Event Organizer";
if ($going_count >= 1) $badges[] = "Event Participant";
if ($donation_count >= 1) $badges[] = "Fund Supporter";
if ($feedback_count >= 1) $badges[] = "Community Reviewer";
if ($points >= 100) $badges[] = "Trusted Contributor";
if (!$badges) $badges[] = "Start by submitting a report";

echo json_encode([
  "ok" => true,
  "name" => $user["full_name"] ?? "User",
  "username" => $user["username"] ?? "",
  "points" => $points,
  "rank" => $rank,
  "level" => $level,
  "badges" => $badges,
  "stats" => [
    "reports" => $report_count,
    "events" => $event_count,
    "going" => $going_count,
    "donations" => $donation_count,
    "feedback" => $feedback_count
  ]
]);
?>
