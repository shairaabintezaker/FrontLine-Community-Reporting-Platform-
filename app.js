const msgBox = () => document.getElementById("formMsg");

function showMsg(text, ok){
  const m = msgBox();
  if(!m) return alert(text);
  m.textContent = text;
  m.style.marginTop = "10px";
  m.style.padding = "10px 12px";
  m.style.borderRadius = "12px";
  m.style.border = ok ? "1px solid #b7e4c7" : "1px solid #f5c2c7";
  m.style.background = ok ? "#ecfdf3" : "#fff1f2";
  m.style.color = ok ? "#0f5132" : "#842029";
}

async function loadIssues(){
  const res = await fetch("issue_list.php");
  const data = await res.json();

  const box = document.getElementById("issueList");
  box.innerHTML = data.map(x => `
    <div class="row">
      <div>
        <b>${x.title}</b>
        <div class="muted">${x.category} • ${x.location} • by ${x.full_name}</div>
        <div class="muted">${Number(x.avg_rating).toFixed(1)} (${x.rating_count}) • Points: ${x.points}</div>
      </div>
      <div>
        <select onchange="rateIssue(${x.id}, this.value)">
          <option value="">Rate</option>
          <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
        </select>
      </div>
    </div>
  `).join("");
}

async function submitIssue(){
  try{
    showMsg("Submitting...", true);

    const fd = new FormData();
    fd.append("title", document.getElementById("t").value.trim());
    fd.append("category", document.getElementById("c").value.trim());
    fd.append("location", document.getElementById("l").value.trim());
    fd.append("description", document.getElementById("d").value.trim());

    const res = await fetch("issue_create.php", { method:"POST", body: fd });
    const txt = (await res.text()).trim();

    if(txt !== "OK"){
      showMsg(txt || ("Error (" + res.status + ")"), false);
      return;
    }

    showMsg("Submitted successfully!", true);
    document.getElementById("issueForm").reset();
    loadIssues();
  }catch(e){
    showMsg("Submit failed! (Check URL: must be http://localhost/87/)", false);
    console.log(e);
  }
}

async function rateIssue(issueId, rating){
  if(!rating) return;

  const fd = new FormData();
  fd.append("issueId", issueId);
  fd.append("rating", rating);

  const res = await fetch("issue_rate.php", { method:"POST", body: fd });
  const txt = (await res.text()).trim();
  if(txt !== "OK") return alert(txt);

  loadIssues();
}

window.onload = function(){
  const btn = document.getElementById("submitBtn");
  if(btn) btn.addEventListener("click", submitIssue);
  loadIssues();
};
