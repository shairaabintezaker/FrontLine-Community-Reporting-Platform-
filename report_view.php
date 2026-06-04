<?php
require_once "db.php";
if(!isset($_SESSION["user"])) { header("Location: login.html?err=Login required"); exit; }

$id = (int)($_GET["id"] ?? 0);
if($id <= 0){ die("Invalid report id"); }

$stmt = $conn->prepare(
  "SELECT sr.*, u.full_name
   FROM service_reports sr
   JOIN users u ON u.id = sr.user_id
   WHERE sr.id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if(!$row){ die("Report not found"); }

function e($s){ return htmlspecialchars((string)($s ?? ""), ENT_QUOTES, "UTF-8"); }

$back_pages = [
  "community" => "community-issues.html",
  "traffic" => "traffic.html",
  "waste" => "waste.html",
  "emergency" => "emergency.html",
  "public-safety" => "public-safety.html",
  "sustainable" => "sustainable.html"
];
$back = $back_pages[$row["service"]] ?? "community-issues.html";

$avg = 0; $count = 0;
$fb = $conn->prepare("SELECT ROUND(IFNULL(AVG(rating),0),1) AS avg_rating, COUNT(*) AS total FROM report_feedback WHERE report_id = ?");
$fb->bind_param("i", $id);
$fb->execute();
if($frow = $fb->get_result()->fetch_assoc()) { $avg = (float)$frow["avg_rating"]; $count = (int)$frow["total"]; }

$comments = [];
$fc = $conn->prepare("SELECT rf.rating, rf.comment, rf.updated_at, u.full_name
                      FROM report_feedback rf JOIN users u ON u.id = rf.user_id
                      WHERE rf.report_id = ? ORDER BY rf.updated_at DESC LIMIT 10");
$fc->bind_param("i", $id);
$fc->execute();
$fres = $fc->get_result();
while($cr = $fres->fetch_assoc()) $comments[] = $cr;

$history = [];
$hs = $conn->prepare("SELECT h.old_status, h.new_status, h.created_at, u.full_name
                      FROM report_status_history h JOIN users u ON u.id = h.changed_by
                      WHERE h.report_id = ? ORDER BY h.id DESC LIMIT 10");
$hs->bind_param("i", $id);
$hs->execute();
$hres = $hs->get_result();
while($hr = $hres->fetch_assoc()) $history[] = $hr;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Report #<?= (int)$row["id"] ?> • Frontline</title>
  <link rel="stylesheet" href="base.css">
  <style>
    .rv{max-width:980px;margin:24px auto;padding:0 14px}
    .rv-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:12px}
    .meta{color:#667085;font-weight:650;margin:10px 0;line-height:1.6}
    .box{border:1px solid rgba(0,0,0,.10);border-radius:14px;padding:12px;background:#fafafa;margin-top:10px}
    pre{white-space:pre-wrap;margin:0;font-family:inherit;line-height:1.55}
    a{color:#2a62ff;font-weight:800}.msg{margin-bottom:10px}.fb-row{border-top:1px solid var(--line);padding:10px 0}.fb-row:first-child{border-top:0}
    @media(max-width:900px){.rv-grid{grid-template-columns:1fr}}
  </style>
  <link rel="stylesheet" href="polish.css" />
</head>
<body>
  <div class="rv">
    <p><a href="<?= e($back) ?>">← Back</a> &nbsp; <a href="authority.html">Open Report Monitor</a></p>
    <?php if(isset($_GET["msg"])): ?><div class="note success msg"><?= e($_GET["msg"]) ?></div><?php endif; ?>
    <div class="rv-grid">
      <div class="card">
        <h2 style="margin:0"><?= e($row["title"]) ?></h2>
        <div class="meta">
          Service: <b><?= e($row["service"]) ?></b> •
          Type: <b><?= e($row["type"]) ?></b> •
          Status: <b><?= e($row["status"]) ?></b><br>
          Submitted by: <b><?= e($row["full_name"]) ?></b> •
          Submitted at: <b><?= e($row["created_at"]) ?></b>
        </div>
        <div class="box"><b>Location:</b> <?= e($row["location"]) ?></div>
        <div class="box"><b>Details</b><pre><?= e($row["details"]) ?></pre></div>
        <div class="box"><b>Community Feedback:</b> <?= $count ? e($avg) . "/5 from " . (int)$count . " response(s)" : "No feedback yet" ?></div>
      </div>

      <aside class="stack">
        <div class="card">
          <h2>Update Progress</h2>
          <p class="muted">Use this to show whether the report is open, being handled, or resolved.</p>
          <form class="form" action="report_status_update.php" method="POST">
            <input type="hidden" name="report_id" value="<?= (int)$row["id"] ?>">
            <select class="in" name="status" required>
              <?php foreach(["Open", "In Progress", "Resolved"] as $st): ?>
                <option value="<?= e($st) ?>" <?= $row["status"] === $st ? "selected" : "" ?>><?= e($st) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn full" type="submit">Save Status</button>
          </form>
        </div>

        <div class="card">
          <h2>Add Feedback</h2>
          <p class="muted">Rate the report quality or leave a short comment.</p>
          <form class="form" id="feedbackForm">
            <input type="hidden" name="report_id" value="<?= (int)$row["id"] ?>">
            <select class="in" name="rating" required>
              <option value="">Rating</option>
              <option value="5">5 - Very helpful</option>
              <option value="4">4 - Helpful</option>
              <option value="3">3 - Average</option>
              <option value="2">2 - Unclear</option>
              <option value="1">1 - Not helpful</option>
            </select>
            <input class="in" name="comment" maxlength="255" placeholder="Comment (optional)">
            <button class="btn full" type="submit">Save Feedback</button>
            <div class="muted" id="feedbackMsg"></div>
          </form>
        </div>
      </aside>
    </div>

    <section class="grid2" style="margin-top:12px">
      <div class="card">
        <h2>Feedback List</h2>
        <?php if(!$comments): ?><p class="muted">No feedback yet.</p><?php endif; ?>
        <?php foreach($comments as $c): ?>
          <div class="fb-row"><b><?= e($c["full_name"]) ?></b> • <span class="pill p-info">★ <?= (int)$c["rating"] ?></span><br><span class="muted"><?= e($c["comment"] ?: "No comment") ?> • <?= e($c["updated_at"]) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <h2>Status History</h2>
        <?php if(!$history): ?><p class="muted">No status changes yet.</p><?php endif; ?>
        <?php foreach($history as $h): ?>
          <div class="fb-row"><b><?= e($h["new_status"]) ?></b><br><span class="muted">Changed by <?= e($h["full_name"]) ?> at <?= e($h["created_at"]) ?></span></div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
<script>
document.getElementById('feedbackForm')?.addEventListener('submit', function(e){
  e.preventDefault();
  const msg = document.getElementById('feedbackMsg');
  msg.textContent = 'Saving...';
  fetch('report_feedback.php', {method:'POST', body:new FormData(this)})
    .then(r => r.json())
    .then(d => {
      msg.textContent = d.message || (d.ok ? 'Saved' : 'Failed');
      if(d.ok) setTimeout(() => location.reload(), 500);
    })
    .catch(() => msg.textContent = 'Could not save feedback.');
});
</script>
</body>
</html>
