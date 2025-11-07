// User data
const user = {
    name: "Chichibest",
    program: "Baccalaureat",
    email: "chichibest@academie.edu",
    profileImage: "images/Chidima Praise Jude Ugwu 12.jpg",
    completedWeeks: JSON.parse(localStorage.getItem('completedWeeks') || '{}'),
    homeworkScores: JSON.parse(localStorage.getItem('homeworkScores') || '{}'),
    quizScores: JSON.parse(localStorage.getItem('quizScores') || '{}'),
    lastLogin: localStorage.getItem('lastLogin') || new Date().toISOString(),
    studyStreak: parseInt(localStorage.getItem('studyStreak') || '0')
};

// Subject data
const subjects = {
    mathematics: { name: "Mathematics", icon: "∫", color: "#003366" },
    physics: { name: "Physics", icon: "⚛", color: "#dc3545" },
    english: { name: "English", icon: "📚", color: "#28a745" },
    economics: { name: "Economics", icon: "💹", color: "#ffc107" },
    biology: { name: "Biology", icon: "🧬", color: "#17a2b8" }
};

// Achievements data
const achievements = [
    { id: "first_week", name: "First Week", desc: "Complete your first week", icon: "🎯", unlocked: true },
    { id: "quiz_master", name: "Quiz Master", desc: "Score 100% on any quiz", icon: "🏆", unlocked: false },
    { id: "homework_hero", name: "Homework Hero", desc: "Submit 5 homeworks", icon: "📝", unlocked: false },
    { id: "streak_7", name: "7-Day Streak", desc: "Study for 7 consecutive days", icon: "🔥", unlocked: false },
    { id: "subject_master", name: "Subject Master", desc: "Complete all weeks in a subject", icon: "⭐", unlocked: false },
    { id: "perfect_score", name: "Perfect Score", desc: "Get 20/20 on homework", icon: "💯", unlocked: false }
];

// DOM elements
const profileImage = document.getElementById('profile-image');
const navbarProfileImg = document.getElementById('navbar-profile-img');
const profileName = document.getElementById('profile-name');
const navbarUsername = document.getElementById('navbar-username');
const profileProgram = document.getElementById('profile-program');
const completedWeeksEl = document.getElementById('completed-weeks');
const averageScoreEl = document.getElementById('average-score');
const totalSubjectsEl = document.getElementById('total-subjects');
const completedHomeworkEl = document.getElementById('completed-homework');
const quizzesTakenEl = document.getElementById('quizzes-taken');
const studyStreakEl = document.getElementById('study-streak');
const progressChart = document.getElementById('progress-chart');
const subjectList = document.getElementById('subject-list');
const badgesGrid = document.getElementById('badges-grid');
const lastUpdatedEl = document.getElementById('last-updated');

// Modal elements
const editProfileModal = document.getElementById('edit-profile-modal');
const changeImageModal = document.getElementById('change-image-modal');
const changeImageBtn = document.getElementById('change-image-btn');
const editProfileBtn = document.getElementById('edit-profile');
const closeEditModal = document.getElementById('close-edit-modal');
const closeImageModal = document.getElementById('close-image-modal');
const cancelEdit = document.getElementById('cancel-edit');
const cancelImage = document.getElementById('cancel-image');
const saveProfile = document.getElementById('save-profile');
const saveImage = document.getElementById('save-image');
const editUsername = document.getElementById('edit-username');
const editProgram = document.getElementById('edit-program');
const editEmail = document.getElementById('edit-email');
const imageUpload = document.getElementById('image-upload');
const imagePreview = document.getElementById('image-preview');
const signoutBtn = document.getElementById('signout-btn');

// Event listeners
changeImageBtn.addEventListener('click', () => showModal(changeImageModal));
editProfileBtn.addEventListener('click', () => showModal(editProfileModal));
closeEditModal.addEventListener('click', () => hideModal(editProfileModal));
closeImageModal.addEventListener('click', () => hideModal(changeImageModal));
cancelEdit.addEventListener('click', () => hideModal(editProfileModal));
cancelImage.addEventListener('click', () => hideModal(changeImageModal));
saveProfile.addEventListener('click', saveProfileChanges);
saveImage.addEventListener('click', saveImageChanges);
imageUpload.addEventListener('change', handleImagePreview);
signoutBtn.addEventListener('click', signOut);

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateStudyStreak();
    loadUserData();
    calculateProgress();
    renderProgressChart();
    renderSubjectProgress();
    renderAchievements();
    updateLastUpdated();
});

// Update study streak
function updateStudyStreak() {
    const today = new Date().toDateString();
    const lastLogin = new Date(user.lastLogin).toDateString();
    
    if (today !== lastLogin) {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    
    if (yesterday.toDateString() === lastLogin) {
        user.studyStreak += 1;
    } else {
        user.studyStreak = 1;
    }
    
    user.lastLogin = new Date().toISOString();
    localStorage.setItem('studyStreak', user.studyStreak.toString());
    localStorage.setItem('lastLogin', user.lastLogin);
    }
}

// Load user data
function loadUserData() {
    profileName.textContent = user.name;
    navbarUsername.textContent = user.name;
    profileProgram.textContent = user.program + " Program";
    editUsername.value = user.name;
    editProgram.value = user.program;
    editEmail.value = user.email;
    
    // Load profile image from localStorage if available
    const savedImage = localStorage.getItem('profileImage');
    if (savedImage) {
    profileImage.src = savedImage;
    navbarProfileImg.src = savedImage;
    }
}

// Calculate progress statistics
function calculateProgress() {
    // Calculate completed weeks
    let totalWeeks = 0;
    let completedWeeks = 0;
    
    Object.keys(user.completedWeeks).forEach(subject => {
    completedWeeks += user.completedWeeks[subject] || 0;
    totalWeeks += Object.keys(subjects).includes(subject) ? 3 : 1; // Assume 3 weeks for main subjects
    });
    
    completedWeeksEl.textContent = completedWeeks;
    
    // Calculate average score
    let totalScore = 0;
    let scoreCount = 0;
    
    // Homework scores
    Object.keys(user.homeworkScores).forEach(subject => {
    Object.keys(user.homeworkScores[subject]).forEach(week => {
        totalScore += user.homeworkScores[subject][week];
        scoreCount++;
    });
    });
    
    // Quiz scores (convert to 20-point scale)
    Object.keys(user.quizScores).forEach(key => {
    const [subject, week, score] = parseQuizKey(key);
    if (score) {
        const [correct, total] = score.split('/').map(Number);
        const percentage = (correct / total) * 20;
        totalScore += percentage;
        scoreCount++;
    }
    });
    
    const averageScore = scoreCount > 0 ? Math.round((totalScore / scoreCount) * 100) / 100 : 0;
    averageScoreEl.textContent = `${averageScore.toFixed(1)}%`;
    
    // Other stats
    totalSubjectsEl.textContent = Object.keys(subjects).length;
    
    // Completed homework count
    let homeworkCount = 0;
    Object.keys(user.homeworkScores).forEach(subject => {
    homeworkCount += Object.keys(user.homeworkScores[subject]).length;
    });
    completedHomeworkEl.textContent = homeworkCount;
    
    // Quizzes taken count
    quizzesTakenEl.textContent = Object.keys(user.quizScores).length;
    
    // Study streak
    studyStreakEl.textContent = user.studyStreak;
}

// Parse quiz key to extract subject, week, and score
function parseQuizKey(key) {
    const parts = key.split('-');
    return [parts[0], parts[2], user.quizScores[key]];
}

// Render progress chart
function renderProgressChart() {
    progressChart.innerHTML = '';
    
    // Sample data for last 6 weeks
    const weeklyData = [65, 75, 80, 85, 78, 90];
    const weekLabels = ['W1', 'W2', 'W3', 'W4', 'W5', 'W6'];
    
    weeklyData.forEach((value, index) => {
    const bar = document.createElement('div');
    bar.className = 'chart-bar';
    bar.style.height = `${value}%`;
    bar.style.background = `linear-gradient(to top, var(--primary), ${lightenColor('#003366', index * 15)})`;
    
    const label = document.createElement('div');
    label.className = 'chart-bar-label';
    label.textContent = weekLabels[index];
    
    bar.appendChild(label);
    progressChart.appendChild(bar);
    });
}

// Render subject progress
function renderSubjectProgress() {
    subjectList.innerHTML = '';
    
    Object.keys(subjects).forEach(subjectKey => {
    const subject = subjects[subjectKey];
    const completed = user.completedWeeks[subjectKey] || 0;
    const totalWeeks = 3; // Assume 3 weeks per subject
    
    const progress = (completed / totalWeeks) * 100;
    
    const subjectItem = document.createElement('div');
    subjectItem.className = 'subject-item';
    
    subjectItem.innerHTML = `
        <div class="subject-icon" style="background: ${subject.color}">${subject.icon}</div>
        <div class="subject-details">
        <div class="subject-name">${subject.name}</div>
        <div class="subject-progress-bar">
            <div class="subject-progress-fill" style="width: ${progress}%; background: ${subject.color};"></div>
        </div>
        <div class="subject-stats">
            <div class="subject-stat">${completed}/${totalWeeks} weeks</div>
            <div class="subject-stat">${Math.round(progress)}% complete</div>
        </div>
        </div>
    `;
    
    subjectList.appendChild(subjectItem);
    });
}

// Render achievements
function renderAchievements() {
    badgesGrid.innerHTML = '';
    
    // Update achievements based on user progress
    updateAchievements();
    
    achievements.forEach(achievement => {
    const badge = document.createElement('div');
    badge.className = `badge ${achievement.unlocked ? '' : 'locked'}`;
    
    badge.innerHTML = `
        <div class="badge-icon">${achievement.icon}</div>
        <div class="badge-name">${achievement.name}</div>
        <div class="badge-desc">${achievement.desc}</div>
    `;
    
    badgesGrid.appendChild(badge);
    });
}

// Update achievements based on user progress
function updateAchievements() {
    // First Week - always unlocked if user has any progress
    achievements[0].unlocked = Object.keys(user.completedWeeks).length > 0;
    
    // Quiz Master - check if any quiz score is 100%
    achievements[1].unlocked = Object.values(user.quizScores).some(score => {
    const [correct, total] = score.split('/').map(Number);
    return correct === total;
    });
    
    // Homework Hero - check if 5 or more homeworks submitted
    let homeworkCount = 0;
    Object.keys(user.homeworkScores).forEach(subject => {
    homeworkCount += Object.keys(user.homeworkScores[subject]).length;
    });
    achievements[2].unlocked = homeworkCount >= 5;
    
    // 7-Day Streak
    achievements[3].unlocked = user.studyStreak >= 7;
    
    // Subject Master - check if any subject has all weeks completed
    achievements[4].unlocked = Object.keys(user.completedWeeks).some(subject => 
    user.completedWeeks[subject] >= 3
    );
    
    // Perfect Score - check if any homework score is 20
    achievements[5].unlocked = Object.keys(user.homeworkScores).some(subject =>
    Object.values(user.homeworkScores[subject]).some(score => score === 20)
    );
}

// Update last updated timestamp
function updateLastUpdated() {
    const now = new Date();
    lastUpdatedEl.textContent = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Show modal
function showModal(modal) {
    modal.style.display = 'flex';
}

// Hide modal
function hideModal(modal) {
    modal.style.display = 'none';
}

// Save profile changes
function saveProfileChanges() {
    user.name = editUsername.value;
    user.program = editProgram.value;
    user.email = editEmail.value;
    
    // Update UI
    profileName.textContent = user.name;
    navbarUsername.textContent = user.name;
    profileProgram.textContent = user.program + " Program";
    
    // Save to localStorage
    localStorage.setItem('userProfile', JSON.stringify({
    name: user.name,
    program: user.program,
    email: user.email
    }));
    
    hideModal(editProfileModal);
    alert('Profile updated successfully!');
}

// Handle image preview
function handleImagePreview(event) {
    const file = event.target.files[0];
    if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.innerHTML = '';
        const img = document.createElement('img');
        img.src = e.target.result;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.borderRadius = '50%';
        img.style.objectFit = 'cover';
        imagePreview.appendChild(img);
    };
    reader.readAsDataURL(file);
    }
}

// Save image changes
function saveImageChanges() {
    const file = imageUpload.files[0];
    if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        // Update profile images
        profileImage.src = e.target.result;
        navbarProfileImg.src = e.target.result;
        
        // Save to localStorage
        localStorage.setItem('profileImage', e.target.result);
        
        hideModal(changeImageModal);
        alert('Profile image updated successfully!');
    };
    reader.readAsDataURL(file);
    } else {
    alert('Please select an image first');
    }
}

// Sign out
function signOut() {
    if (confirm('Are you sure you want to sign out?')) {
    // In a real app, this would clear session and redirect to login
    alert('Signed out successfully!');
    // window.location.href = 'login.html';
    }
}

// Utility function to lighten color
function lightenColor(color, percent) {
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) + amt;
    const G = (num >> 8 & 0x00FF) + amt;
    const B = (num & 0x0000FF) + amt;
    return "#" + (
    0x1000000 +
    (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
    (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
    (B < 255 ? B < 1 ? 0 : B : 255)
    ).toString(16).slice(1);
}
