function loadProfile(){
  fetch("profile_api.php", {cache:"no-store"})
    .then(r => r.json())
    .then(d => {
      if(!d.ok) return;
      const name = document.getElementById("profileName");
      const points = document.getElementById("profilePoints");
      const rank = document.getElementById("profileRank");
      const level = document.getElementById("profileLevel");
      const badges = document.getElementById("badgeList");
      const stats = document.getElementById("profileStats");
      if(name) name.textContent = d.name;
      if(points) points.textContent = d.points;
      if(rank) rank.textContent = `#${d.rank}`;
      if(level) level.textContent = d.level;
      if(badges){
        badges.innerHTML = (d.badges || []).map(b => `<span class="pill p-ok">${String(b).replace(/[&<>"']/g, m => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}</span>`).join(" ");
      }
      if(stats){
        const s = d.stats || {};
        stats.innerHTML = `
          <div class="row"><span>Reports submitted</span><b>${Number(s.reports || 0)}</b></div>
          <div class="row"><span>Events created</span><b>${Number(s.events || 0)}</b></div>
          <div class="row"><span>Going responses</span><b>${Number(s.going || 0)}</b></div>
          <div class="row"><span>Donations recorded</span><b>${Number(s.donations || 0)}</b></div>
          <div class="row"><span>Feedback given</span><b>${Number(s.feedback || 0)}</b></div>`;
      }
    })
    .catch(() => {});
}
window.addEventListener("load", () => {
  loadProfile();
  setInterval(loadProfile, 7000);
});
