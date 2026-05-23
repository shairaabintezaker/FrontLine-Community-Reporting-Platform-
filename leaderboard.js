window.onload = function(){
  loadLeaderboard();
};

function loadLeaderboard(){
  fetch("leaderboard.php")
    .then(r => r.json())
    .then(data => {
      var p = document.getElementById("podiumBox");
      var top3 = data.slice(0,3);
      p.innerHTML = top3.map((u, i) => `
        <div class="box">
          <span class="rank">#${i+1}</span>
          <h3>${u.full_name}</h3>
          <p>${u.points} points</p>
        </div>
      `).join("") || p.innerHTML;

      var box = document.getElementById("lbBody");
      var html = "";
      for(var i=0;i<data.length;i++){
        var u = data[i];
        html += `
          <tr>
            <td>#${i+1}</td>
            <td>${u.full_name}</td>
            <td>${u.username}</td>
            <td>${u.points}</td>
          </tr>
        `;
      }
      box.innerHTML = html || `<tr><td colspan="4">No data</td></tr>`;
      document.getElementById("yourRank").innerText = "Your Rank: (auto soon)";
    });
}
