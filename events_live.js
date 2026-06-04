let activeEventType = "All";
let lastEventsJson = "";

function eventEsc(s){
  return String(s ?? "").replace(/[&<>"']/g, m => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]));
}
function dayLabel(dateStr){
  const d = new Date(dateStr + "T00:00:00");
  return d.toLocaleString("en", {weekday:"short"}).toUpperCase();
}
function timeLabel(t){ return String(t || "").slice(0,5); }

function renderEvents(list){
  const box = document.getElementById("eventList");
  const count = document.getElementById("eventCount");
  if(!box) return;
  if(count) count.textContent = `${list.length} Event${list.length === 1 ? "" : "s"}`;
  if(!list.length){
    box.innerHTML = `<div class="event"><div><b>No events found</b><small>Create an event from the form.</small></div></div>`;
    return;
  }
  box.innerHTML = list.map(e => {
    const goingClass = e.my_response === "Going" ? "active-choice" : "";
    const interestedClass = e.my_response === "Interested" ? "active-choice" : "";
    return `
      <div class="event" data-event-id="${Number(e.id)}">
        <div class="date">${dayLabel(e.event_date)}<br>${timeLabel(e.event_time)}</div>
        <div>
          <b>${eventEsc(e.title)}</b>
          <small>${eventEsc(e.location)} • ${eventEsc(e.details)}</small>
          <div class="meta">Created by ${eventEsc(e.organizer_name)} • ${eventEsc(e.event_date)}</div>
          <div class="event-actions">
            <span class="pill p-warn">${eventEsc(e.type)}</span>
            <button class="pill p-ok action-pill ${goingClass}" type="button" onclick="respondEvent(${Number(e.id)}, 'Going')">Going (${Number(e.going_count || 0)})</button>
            <button class="pill p-info action-pill ${interestedClass}" type="button" onclick="respondEvent(${Number(e.id)}, 'Interested')">Interested (${Number(e.interested_count || 0)})</button>
          </div>
        </div>
      </div>`;
  }).join("");
}

function loadEvents(silent){
  const url = `events_api.php?type=${encodeURIComponent(activeEventType)}&limit=50`;
  fetch(url, {cache:"no-store"})
    .then(r => r.json())
    .then(list => {
      if(!Array.isArray(list)) list = [];
      const json = JSON.stringify(list);
      if(json !== lastEventsJson || !silent){
        lastEventsJson = json;
        renderEvents(list);
      }
    })
    .catch(() => {
      if(!silent){
        const box = document.getElementById("eventList");
        if(box) box.innerHTML = `<div class="event"><div><b>Could not load events</b><small>Use XAMPP/Apache so PHP can connect to MySQL.</small></div></div>`;
      }
    });
}

function bindEventFilters(){
  document.querySelectorAll("#eventFilters [data-type]").forEach(btn => {
    btn.addEventListener("click", () => {
      activeEventType = btn.getAttribute("data-type") || "All";
      document.querySelectorAll("#eventFilters [data-type]").forEach(b => b.classList.remove("on"));
      btn.classList.add("on");
      loadEvents(false);
    });
  });
}

window.addEventListener("load", () => {
  bindEventFilters();
  loadEvents(false);
  setInterval(() => loadEvents(true), 5000);
});
