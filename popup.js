// Function to show popup after 2 seconds
setTimeout(function() {
  showPopup();
}, 2000);

// Function to show popup
function showPopup() {
  document.getElementById('popup').style.display = 'block';
}

// Function to close popup
function closePopup() {
  document.getElementById('popup').style.display = 'none';
}

// Function to show popup randomly between 2 to 5 minutes
function showPopupRandomly() {
  const randomTime = Math.floor(Math.random() * (300000 - 120000 + 1)) + 120000; // Random time between 2 to 5 minutes in milliseconds (1 minute = 60000 milliseconds)
  setTimeout(function() {
    showPopup();
    showPopupRandomly();
  }, randomTime);
}

// Call the function to show popup randomly
showPopupRandomly();
