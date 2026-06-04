<?php
require_once "db.php";

if (!isset($_SESSION["user"])) {
  header("Location: login.html?err=Login required");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: services.html");
  exit;
}

$id = (int)($_POST["report_id"] ?? 0);
$new_status = trim($_POST["status"] ?? "");
$return = trim($_POST["return"] ?? "");
$allowed_status = ["Open", "In Progress", "Resolved"];

if ($id <= 0 || !in_array($new_status, $allowed_status, true)) {
  header("Location: services.html?err=Invalid status update");
  exit;
}

$stmt = $conn->prepare("SELECT status FROM service_reports WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
  header("Location: services.html?err=Report not found");
  exit;
}

$old_status = $row["status"];
if ($old_status !== $new_status) {
  $up = $conn->prepare("UPDATE service_reports SET status = ? WHERE id = ?");
  $up->bind_param("si", $new_status, $id);
  $up->execute();

  $uid = (int)$_SESSION["user"]["id"];
  $hist = $conn->prepare("INSERT INTO report_status_history(report_id, old_status, new_status, changed_by) VALUES(?, ?, ?, ?)");
  $hist->bind_param("issi", $id, $old_status, $new_status, $uid);
  $hist->execute();
}

if ($return === "authority.html") {
  header("Location: authority.html?msg=Status updated");
} else {
  header("Location: report_view.php?id=" . $id . "&msg=Status updated");
}
exit;
?>
