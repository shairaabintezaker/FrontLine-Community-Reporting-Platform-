<?php
require_once "db.php";
if (!isset($_SESSION['user'])) {
  header("Location: login.html?err=Please login first");
  exit;
}
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Events • Frontline</title>
<link rel="stylesheet" href="base.css">
<link rel="stylesheet" href="events.css">
  <link rel="stylesheet" href="polish.css" />
</head>
<body>
<header class="top">
  <div class="wrap bar">
    <div class="brand"><i></i>Frontline</div>
    <nav class="nav">
      <a href="home.html">Home</a>
      <a href="services.html">Services</a>
      <a class="on" href="events.php">Events</a>
      <a href="rewards.html">Rewards</a>
    </nav>
  </div>
</header>

<main class="wrap main">
  <div class="pagehead">
    <div>
      <div class="breadcrumb"><b>Services</b> / Events</div>
      <div class="title">Events & Activities</div>
      <div class="sub">Create events and respond with Going or Interested. Counts update from the database.</div>
      <div class="filters" id="eventFilters">
        <button class="on" type="button" data-type="All">All</button>
        <button type="button" data-type="Upcoming">Upcoming</button>
        <button type="button" data-type="Volunteer">Volunteer</button>
        <button type="button" data-type="Awareness">Awareness</button>
        <button type="button" data-type="Environment">Environment</button>
        <button type="button" data-type="Meetup">Meetup</button>
      </div>
    </div>
    <span class="pill p-info" id="eventCount">Loading...</span>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="note success"><?php echo h($_GET['success']); ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="note error"><?php echo h($_GET['error']); ?></div>
  <?php endif; ?>
  <div class="note" id="eventMsg" style="display:none"></div>

  <section class="grid2">
    <div class="card">
      <h2>Upcoming Events <span class="pill p-ok">Live database list</span></h2>
      <div class="egrid" id="eventList">
        <div class="event"><div><b>Loading events...</b><small>Please wait.</small></div></div>
      </div>
    </div>

    <aside class="stack">
      <div class="card">
        <h2>Create an Event</h2>
        <form class="form" action="event_create.php" method="POST">
          <input class="in" name="title" required placeholder="Event title (ex: Clean-up Drive)">
          <select class="in" name="type" required>
            <option value="">Event type</option>
            <option>Upcoming</option>
            <option>Volunteer</option>
            <option>Awareness</option>
            <option>Environment</option>
            <option>Meetup</option>
          </select>
          <input class="in" name="location" required placeholder="Location (Area / Landmark)">
          <input class="in" name="event_date" type="date" required>
          <input class="in" name="event_time" type="time" required>
          <textarea class="in ta" name="details" required placeholder="Short description..."></textarea>
          <button class="btn full" type="submit">Create Event</button>
        </form>
      </div>

      <div class="card">
        <h2>Participation Summary</h2>
        <div class="list">
          <div class="row"><span>Create event</span><b>+25</b></div>
          <div class="row"><span>Mark Going</span><b>+10 once</b></div>
          <div class="row"><span>Interested response</span><b>Saved</b></div>
        </div>
      </div>
    </aside>
  </section>
</main>

<footer class="foot">
  <div class="wrap fbar"><span>© 2025 Frontline</span><span>Events</span></div>
</footer>
<script src="event_buttons.js"></script>
<script src="events_live.js"></script>
</body>
</html>
