window.onload = function() {
  const progressPath = document.querySelector('.progress');
  const percentageText = document.querySelector('.percentage');
  const percent = 60; // Example: you can make this dynamic later

  // Animate circle using stroke-dasharray
  progressPath.setAttribute('stroke-dasharray', `${percent}, 100`);
  percentageText.textContent = `${percent}%`;
};
