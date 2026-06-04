<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Login required"]);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["ok" => false, "message" => "Invalid request"]);
  exit;
}

$report_id = (int)($_POST["report_id"] ?? 0);
$rating = (int)($_POST["rating"] ?? 0);
$comment = trim($_POST["comment"] ?? "");
$user_id = (int)$_SESSION["user"]["id"];

if ($report_id <= 0 || $rating < 1 || $rating > 5) {
  echo json_encode(["ok" => false, "message" => "Choose a rating from 1 to 5"]);
  exit;
}
if (mb_strlen($comment) > 255) {
  echo json_encode(["ok" => false, "message" => "Comment must be 255 characters or less"]);
  exit;
}

$check = $conn->prepare("SELECT id FROM service_reports WHERE id = ? LIMIT 1");
$check->bind_param("i", $report_id);
$check->execute();
if (!$check->get_result()->fetch_assoc()) {
  echo json_encode(["ok" => false, "message" => "Report not found"]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO report_feedback(report_id, user_id, rating, comment)
                        VALUES(?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()");
$stmt->bind_param("iiis", $report_id, $user_id, $rating, $comment);
$ok = $stmt->execute();

echo json_encode(["ok" => $ok, "message" => $ok ? "Feedback saved" : "Could not save feedback"]);
?>
