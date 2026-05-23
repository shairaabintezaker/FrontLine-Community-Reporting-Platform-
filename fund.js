window.addEventListener("load", loadFund);

function loadFund(){
  fetch("fund_list.php")
    .then(r => r.json())
    .then(d => {
      // total
      document.getElementById("totalBox").innerText = "Total: ৳" + d.total;
      if(typeof setGoalBar === "function") setGoalBar(Number(d.total || 0));

      // purpose summary
      var pbox = document.getElementById("purposeList");
      var phtml = "";
      (d.purpose || []).forEach(function(x){
        phtml += '<div class="row"><span>' + esc(x.purpose) + '</span><b>৳' + x.sum + '</b></div>';
      });
      pbox.innerHTML = phtml || '<div class="row"><span class="muted">No data</span></div>';

      // recent donations
      var box = document.getElementById("donationList");
      var html = "";
      (d.recent || []).forEach(function(x){
        html += ''
          + '<div class="row">'
          + '  <div>'
          + '    <b>৳' + x.amount + '</b>'
          + '    <div class="muted">' + esc(x.purpose) + ' • by ' + esc(x.full_name) + '</div>'
          + '  </div>'
          + '  <span class="pill p-info">' + esc(x.created_at) + '</span>'
          + '</div>';
      });
      box.innerHTML = html || '<div class="row"><span class="muted">No donations yet</span></div>';
    })
    .catch(() => {
      document.getElementById("donationList").innerHTML =
        '<div class="row"><span class="muted">Could not load data</span></div>';
    });
}

function donate(){
  var msg = document.getElementById("fundMsg");
  if(msg){ msg.textContent = ""; }

  var amt = Number(document.getElementById("amt").value || 0);
  var purpose = document.getElementById("purpose").value;
  var note = document.getElementById("note").value;

  if(amt < 10) return showMsg("Minimum ৳10 required");
  if(!purpose) return showMsg("Select a purpose");

  var fd = new FormData();
  fd.append("amount", amt);
  fd.append("purpose", purpose);
  fd.append("note", note);

  fetch("fund_donate.php", { method:"POST", body:fd })
    .then(r => r.text())
    .then(txt => {
      txt = (txt || "").trim();
      if(txt !== "OK") return showMsg(txt || "Failed");

      // clear
      document.getElementById("amt").value = "";
      document.getElementById("purpose").value = "";
      document.getElementById("note").value = "";
      if(typeof previewReceipt === "function") previewReceipt();

      showMsg("Donation successful!", true);
      loadFund();
    })
    .catch(() => showMsg("Network error"));
  return false;
}

function showMsg(t, ok){
  var msg = document.getElementById("fundMsg");
  if(!msg){ alert(t); return; }
  msg.textContent = t;
  msg.style.color = ok ? "#1f915c" : "#b42318";
}

function esc(s){
  return String(s ?? "").replace(/[&<>"']/g, m => ({
    "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"
  }[m]));
}
