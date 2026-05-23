function statusClass(s){
  s = (s || "").toLowerCase();
  if(s.includes("progress")) return "s-prog";
  if(s.includes("resolv")) return "s-done";
  return "s-open";
}
function firstLetter(name){
  return (name && name.trim()) ? name.trim().charAt(0).toUpperCase() : "U";
}

function loadRecentReports(){
  fetch("reports_list.php")
    .then(r => r.json())
    .then(list => {
      const box = document.getElementById("recentReports");
      if(!box) return;

      if(!list.length){
        box.innerHTML = `<div class="muted" style="padding:12px">No reports yet.</div>`;
        return;
      }

      box.innerHTML = list.map(x => `
        <article class="post">
          <div class="phead">
            <div class="who">
              <div class="av">${firstLetter(x.full_name)}</div>
              <div>
                <div class="name">${x.full_name} <span class="muted">• ${x.location}</span></div>
                <div class="meta">${(x.service||"").replace(/_/g," ")} • ${x.type} • ${x.created_at}</div>
              </div>
            </div>

            <a class="status ${statusClass(x.status)}" href="report_view.php?id=${x.id}">
              ${x.status || "Open"}
            </a>
          </div>

          <h3 class="ptitle">${x.title}</h3>
          <p class="pdesc">${(x.details||"").slice(0,160)}${(x.details && x.details.length>160) ? "..." : ""}</p>
        </article>
      `).join("");
    })
    .catch(() => {
      const box = document.getElementById("recentReports");
      if(box) box.innerHTML = `<div class="muted" style="padding:12px">Failed to load reports.</div>`;
    });
}
