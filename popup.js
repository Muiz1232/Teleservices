// Function to show popup after 2 seconds
setTimeout(function() {
  document.getElementById('popup').style.display = 'block';
}, 2000);

// Function to close popup
function closePopup() {
  document.getElementById('popup').style.display = 'none';
}

// Function to show popup again if the user stays for more than 1 minute
let showPopupTimeout;
function showPopupAgain() {
  showPopupTimeout = setTimeout(function() {
    document.getElementById('popup').style.display = 'block';
  }, 30000); // 1 minute in milliseconds
}

// Call the function to start the timeout
showPopupAgain();

// Add an event listener to reset the timeout when the user interacts with the page
document.addEventListener('mousemove', function() {
  clearTimeout(showPopupTimeout);
  showPopupAgain();
});
