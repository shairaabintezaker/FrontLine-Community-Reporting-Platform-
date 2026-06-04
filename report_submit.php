<?php
require_once "db.php";

$allowed_returns = [
  "home.html", "services.html", "community-issues.html", "traffic.html", "waste.html",
  "emergency.html", "public-safety.html", "sustainable.html", "events.php",
  "leaderboard.html", "fund_collection.html", "rewards.html"
];

function safe_return_page($page, $allowed_returns) {
  $page = trim($page ?? "home.html");
  return in_array($page, $allowed_returns, true) ? $page : "home.html";
}

function go_back($page, $params = []) {
  $query = $params ? ("?" . http_build_query($params)) : "";
  header("Location: " . $page . $query);
  exit;
}

$return = safe_return_page($_POST["return"] ?? "home.html", $allowed_returns);

if (!isset($_SESSION["user"])) {
  go_back("login.html", ["err" => "Login required"]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  go_back($return, ["err" => "Invalid request"]);
}

$service  = trim($_POST["service"] ?? "");
$title    = trim($_POST["title"] ?? "");
$type     = trim($_POST["type"] ?? "");
$location = trim($_POST["location"] ?? "");
$details  = trim($_POST["details"] ?? "");

if ($service === "" || $title === "" || $type === "" || $location === "" || $details === "") {
  go_back($return, ["err" => "Fill all fields"]);
}

$uid = (int)$_SESSION["user"]["id"];

$stmt = $conn->prepare(
  "INSERT INTO service_reports(user_id, service, title, type, location, details)
   VALUES(?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssss", $uid, $service, $title, $type, $location, $details);

if ($stmt->execute()) {
  $points_by_service = [
    "community" => 10,
    "traffic" => 10,
    "waste" => 10,
    "public-safety" => 10,
    "sustainable" => 10,
    "emergency" => 20
  ];

  $add = $points_by_service[$service] ?? 0;
  if ($add > 0) {
    $up = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $up->bind_param("ii", $add, $uid);
    $up->execute();

    if (isset($_SESSION["user"]["points"])) {
      $_SESSION["user"]["points"] += $add;
    }
  }

  go_back($return, ["msg" => "Submitted successfully"]);
}

go_back($return, ["err" => "DB failed"]);
?>
