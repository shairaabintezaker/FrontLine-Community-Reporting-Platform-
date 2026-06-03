function esc(s){
  return String(s ?? "").replace(/[&<>"']/g, m => ({
    "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;", "'":"&#39;"
  }[m]));
}

function shortMonth(dateStr){
  const d = new Date(dateStr + "T00:00:00");
  return d.toLocaleString("en", {month:"short"}).toUpperCase();
}
function dayNo(dateStr){
  const d = new Date(dateStr + "T00:00:00");
  return String(d.getDate()).padStart(2, "0");
}

function loadHomeDashboard(){
  fetch("dashboard_api.php", {cache:"no-store"})
    .then(r => r.json())
    .then(d => {
      if(!d.ok) return;
      const reports = document.getElementById("dashReports");
      const points = document.getElementById("dashPoints");
      const events = document.getElementById("dashEvents");
      if(reports) reports.textContent = d.reports_week;
      if(points) points.textContent = d.points;
      if(events) events.textContent = d.upcoming_events;

      const eventText = document.getElementById("highlightEvent");
      if(eventText){
        if(d.latest_event){
          eventText.innerHTML = `<b>${esc(d.latest_event.title)}</b> on ${esc(d.latest_event.event_date)} at ${esc(String(d.latest_event.event_time).slice(0,5))}.`;
        }else{
          eventText.innerHTML = `<b>No upcoming event yet.</b> Create one from the Events page.`;
        }
      }
    })
    .catch(() => {});
}

function loadHomeEvents(){
  const box = document.getElementById("homeEvents");
  if(!box) return;

  fetch("events_api.php?limit=3", {cache:"no-store"})
    .then(r => r.json())
    .then(list => {
      if(!Array.isArray(list) || !list.length){
        box.innerHTML = `<div class="ev"><div><h4>No upcoming events yet</h4><p>Create the first community event from the Events page.</p></div><a class="b b1" href="events.php">Create</a></div>`;
        return;
      }
      box.innerHTML = list.map((e, i) => `
        <a class="ev" href="events.php">
          <div class="date"><b>${shortMonth(e.event_date)}</b><span>${dayNo(e.event_date)}</span></div>
          <div><h4>${esc(e.title)}</h4><p>${esc(e.location)} • ${esc(String(e.event_time).slice(0,5))} • Going ${Number(e.going_count || 0)}</p></div>
          <i class="b b${(i % 3) + 1}">${esc(e.type)}</i>
        </a>
      `).join("");
    })
    .catch(() => {
      box.innerHTML = `<div class="ev"><div><h4>Could not load events</h4><p>Run the project through XAMPP/Apache so PHP can connect to MySQL.</p></div></div>`;
    });
}

window.addEventListener("load", () => {
  loadHomeDashboard();
  loadHomeEvents();
  setInterval(() => { loadHomeDashboard(); loadHomeEvents(); }, 5000);
});
