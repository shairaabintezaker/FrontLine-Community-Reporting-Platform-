<?php
require_once "db.php";

if (!isset($_SESSION['user'])) {
  header("Location: login.html?err=Login required");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: events.php");
  exit;
}

$title = trim($_POST['title'] ?? '');
$type = trim($_POST['type'] ?? '');
$location = trim($_POST['location'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$event_time = trim($_POST['event_time'] ?? '');
$details = trim($_POST['details'] ?? '');
$organizer_id = (int)$_SESSION['user']['id'];

$allowed_types = ['Upcoming', 'Volunteer', 'Awareness', 'Environment', 'Meetup'];

if ($title === '' || $type === '' || $location === '' || $event_date === '' || $event_time === '' || $details === '') {
  header("Location: events.php?error=" . urlencode("Fill all fields"));
  exit;
}

if (!in_array($type, $allowed_types, true)) {
  header("Location: events.php?error=" . urlencode("Invalid event type"));
  exit;
}

$stmt = $conn->prepare("INSERT INTO events (title, type, location, event_date, event_time, details, organizer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssi", $title, $type, $location, $event_date, $event_time, $details, $organizer_id);

if ($stmt->execute()) {
  $add = 25;
  $up = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
  $up->bind_param("ii", $add, $organizer_id);
  $up->execute();
  if (isset($_SESSION['user']['points'])) {
    $_SESSION['user']['points'] += $add;
  }
  header("Location: events.php?success=" . urlencode("Event created. You earned 25 points."));
  exit;
}

header("Location: events.php?error=" . urlencode("Database error"));
exit;
?>
