function protectHome(){
  fetch("me.php")
    .then(r => r.json())
    .then(d => {
      if(!d.ok){
        location.href = "login.html?err=Please login first";
      }
    });
}