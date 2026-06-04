<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['user'])) {
  echo json_encode(['status' => 'error', 'message' => 'Login required']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
  exit;
}

$event_id = (int)($_POST['event_id'] ?? 0);
$response_type = trim($_POST['response_type'] ?? '');
$user_id = (int)$_SESSION['user']['id'];

if ($event_id <= 0 || !in_array($response_type, ['Going', 'Interested'], true)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid response']);
  exit;
}

$check = $conn->prepare("SELECT id FROM events WHERE id = ? LIMIT 1");
$check->bind_param("i", $event_id);
$check->execute();
if (!$check->get_result()->fetch_assoc()) {
  echo json_encode(['status' => 'error', 'message' => 'Event not found']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO event_responses (event_id, user_id, response_type)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE response_type = VALUES(response_type), responded_at = NOW()");
$stmt->bind_param("iis", $event_id, $user_id, $response_type);
$stmt->execute();

$points_added = 0;
if ($response_type === 'Going') {
  $points = 10;
  $action = 'going';
  $log = $conn->prepare("INSERT IGNORE INTO event_points(event_id, user_id, action, points) VALUES (?, ?, ?, ?)");
  $log->bind_param("iisi", $event_id, $user_id, $action, $points);
  $log->execute();
  if ($log->affected_rows > 0) {
    $up = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $up->bind_param("ii", $points, $user_id);
    $up->execute();
    if (isset($_SESSION['user']['points'])) {
      $_SESSION['user']['points'] += $points;
    }
    $points_added = $points;
  }
}

$countStmt = $conn->prepare("SELECT response_type, COUNT(*) AS total FROM event_responses WHERE event_id = ? GROUP BY response_type");
$countStmt->bind_param("i", $event_id);
$countStmt->execute();
$countRes = $countStmt->get_result();
$going_count = 0;
$interested_count = 0;
while ($row = $countRes->fetch_assoc()) {
  if ($row['response_type'] === 'Going') $going_count = (int)$row['total'];
  if ($row['response_type'] === 'Interested') $interested_count = (int)$row['total'];
}

echo json_encode([
  'status' => 'success',
  'going_count' => $going_count,
  'interested_count' => $interested_count,
  'points_added' => $points_added
]);
?>
