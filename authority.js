let authorityReports = [];
let lastAuthorityJson = "";

function aesc(s){ return String(s ?? "").replace(/[&<>"']/g, m => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m])); }
function statusClass(s){ s=(s||"").toLowerCase(); if(s.includes("progress")) return "s-prog"; if(s.includes("resolv")) return "s-done"; return "s-open"; }
function serviceName(s){ return String(s || "").replace(/-/g, " "); }

function renderAuthority(){
  const box = document.getElementById("authorityList");
  const q = (document.getElementById("authoritySearch")?.value || "").toLowerCase().trim();
  const service = document.getElementById("authorityService")?.value || "All";
  const status = document.getElementById("authorityStatus")?.value || "All";
  let list = authorityReports.filter(r => {
    const serviceOk = service === "All" || r.service === service;
    const statusOk = status === "All" || r.status === status;
    const text = `${r.title} ${r.type} ${r.location} ${r.details} ${r.full_name}`.toLowerCase();
    return serviceOk && statusOk && (!q || text.includes(q));
  });
  if(!list.length){ box.innerHTML = `<div class="muted" style="padding:12px">No matching reports.</div>`; return; }
  box.innerHTML = list.map(r => `
    <article class="post">
      <div class="phead">
        <div class="who"><div class="av">${aesc((r.full_name||'U').charAt(0).toUpperCase())}</div><div><div class="name">${aesc(r.title)}</div><div class="meta">${aesc(serviceName(r.service))} • ${aesc(r.type)} • ${aesc(r.location)} • submitted ${aesc(r.created_at)} by ${aesc(r.full_name)}</div></div></div>
        <a class="status ${statusClass(r.status)}" href="report_view.php?id=${Number(r.id)}">${aesc(r.status)}</a>
      </div>
      <p class="pdesc">${aesc((r.details || '').slice(0,190))}${r.details && r.details.length>190?'...':''}</p>
      <form class="inline-status" onsubmit="return updateReportStatus(event, ${Number(r.id)});">
        <select class="in" name="status">
          <option ${r.status==='Open'?'selected':''}>Open</option>
          <option ${r.status==='In Progress'?'selected':''}>In Progress</option>
          <option ${r.status==='Resolved'?'selected':''}>Resolved</option>
        </select>
        <button class="btn" type="submit">Update Status</button>
        <a class="btn ghost" href="report_view.php?id=${Number(r.id)}">Details / Feedback</a>
      </form>
    </article>`).join('');
}

function loadAuthority(silent){
  fetch('reports_list.php', {cache:'no-store'})
    .then(r => r.json())
    .then(list => {
      authorityReports = Array.isArray(list) ? list : [];
      const json = JSON.stringify(authorityReports);
      if(json !== lastAuthorityJson || !silent){ lastAuthorityJson = json; renderAuthority(); }
    })
    .catch(() => { if(!silent) document.getElementById('authorityList').innerHTML = `<div class="muted" style="padding:12px">Could not load reports.</div>`; });
}

function updateReportStatus(e, id){
  e.preventDefault();
  const form = e.target;
  const fd = new FormData();
  fd.append('report_id', id);
  fd.append('status', form.status.value);
  fd.append('return', 'authority.html');
  fetch('report_status_update.php', {method:'POST', body:fd})
    .then(() => {
      const msg = document.getElementById('authorityMsg');
      msg.style.display = 'block'; msg.className = 'note success'; msg.textContent = 'Status updated.';
      loadAuthority(false);
      setTimeout(() => msg.style.display='none', 2000);
    })
    .catch(() => {
      const msg = document.getElementById('authorityMsg');
      msg.style.display = 'block'; msg.className = 'note error'; msg.textContent = 'Could not update status.';
    });
  return false;
}

document.addEventListener('DOMContentLoaded', () => {
  ['authoritySearch','authorityService','authorityStatus'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', renderAuthority);
    document.getElementById(id)?.addEventListener('change', renderAuthority);
  });
  loadAuthority(false);
  setInterval(() => loadAuthority(true), 5000);
});
