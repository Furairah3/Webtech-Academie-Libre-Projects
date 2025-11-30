
// Simulated user profile
const user = {
  name: "Chichibest",
  simulatedProgram: localStorage.getItem('simProgram') || 'Baccalaureat',
  completedWeeks: JSON.parse(localStorage.getItem('completedWeeks') || '{}')
};

// Updated course content with actual video IDs/URLs
const courseContent = {
  mathematics: {
    weeks: [
      { 
        week: 1, 
        title: 'Introduction to Algebra', 
        videoId: 'NybHckSEQBI', // YouTube video ID for Algebra basics
        videoTitle: 'Algebra Basics: What Is Algebra?',
        note: 'Algebra_Basics.pdf', 
        extra: { title: 'Algebra by Example', link: '#' }, 
        exams: [{ year: 2023, file: 'BEPC_Math_2023.pdf' }] 
      },
      { 
        week: 2, 
        title: 'Linear Equations', 
        videoId: 'Vct3K-6w72U', // YouTube video ID for Linear Equations
        videoTitle: 'Linear Equations - Algebra',
        note: 'Linear_Equations.pdf', 
        extra: { title: 'Khan Academy Algebra', link: 'https://www.khanacademy.org/math/algebra' }, 
        exams: [{ year: 2022, file: 'BEPC_Math_2022.pdf' }] 
      },
      { 
        week: 3, 
        title: 'Quadratic Equations', 
        videoId: 'IVG2WqWsQA4', // YouTube video ID for Quadratic Equations
        videoTitle: 'How To Solve Quadratic Equations',
        note: 'Quadratic_Notes.pdf', 
        extra: { title: 'Advanced Problems', link: '#' }, 
        exams: [{ year: 2021, file: 'BEPC_Math_2021.pdf' }] 
      }
    ]
  },
  physics: {
    weeks: [
      { 
        week: 1, 
        title: 'Kinematics', 
        videoId: 'ox-JXp-mr1E', // YouTube video ID for Kinematics
        videoTitle: 'Kinematics Physics',
        note: 'Kinematics.pdf', 
        extra: { title: 'Physics Textbook', link: '#' }, 
        exams: [{ year: 2023, file: 'BEPC_Physics_2023.pdf' }] 
      }
    ]
  },
  english: {
    weeks: [
      { 
        week: 1, 
        title: 'Grammar Basics', 
        videoId: '4PvL1Vrlc4k', // YouTube video ID for English Grammar
        videoTitle: 'Basic English Grammar',
        note: 'Grammar_Basics.pdf', 
        extra: { title: 'English Exercises', link: '#' }, 
        exams: [{ year: 2023, file: 'BEPC_English_2023.pdf' }] 
      }
    ]
  },
  economics: {
    weeks: [
      { 
        week: 1, 
        title: 'Supply and Demand', 
        videoId: 'LW6RgOXy8d0', // YouTube video ID for Supply & Demand
        videoTitle: 'Supply and Demand Explained',
        note: 'Supply_Demand.pdf', 
        extra: { title: 'Economic Principles', link: '#' }, 
        exams: [{ year: 2023, file: 'BEPC_Economics_2023.pdf' }] 
      }
    ]
  },
  biology: {
    weeks: [
      { 
        week: 1, 
        title: 'Cell Structure', 
        videoId: 'URUJD5NEXC8', // YouTube video ID for Cell Biology
        videoTitle: 'Biology: Cell Structure',
        note: 'Cell_Structure.pdf', 
        extra: { title: 'Biology Diagrams', link: '#' }, 
        exams: [{ year: 2023, file: 'BEPC_Biology_2023.pdf' }] 
      }
    ]
  }
};

// Store current subject globally to track which subject we're viewing
let currentSubjectId = '';

// Initialize UI
document.addEventListener('DOMContentLoaded', function() {
  console.log('Page loaded - initializing resources...');
  
  // Set user program
  document.getElementById('user-program').textContent = user.simulatedProgram;

  // Back button handler
  document.getElementById('back-to-subjects').addEventListener('click', function() {
    console.log('Back to subjects clicked');
    document.getElementById('course-view').classList.add('hidden');
    document.getElementById('subjects-view').classList.remove('hidden');
    currentSubjectId = ''; // Reset current subject
  });

  // Tab handlers
  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const tabName = this.dataset.tab;
      showTab(tabName);
    });
  });

  // Add click handlers to View Materials buttons
  document.querySelectorAll('.btn-primary').forEach(button => {
    if (button.textContent === 'View Materials') {
      button.addEventListener('click', function() {
        console.log('View Materials clicked');
        const subjectCard = this.closest('.subject-card');
        const subjectTitle = subjectCard.querySelector('h3').textContent;
        console.log('Opening course:', subjectTitle);
        openCourse(subjectTitle.toLowerCase(), subjectTitle);
      });
    }
  });

  // Add click handlers to sidebar subject buttons
  document.querySelectorAll('#subject-list-small .btn-ghost').forEach(button => {
    button.addEventListener('click', function() {
      const subjectTitle = this.textContent;
      console.log('Sidebar subject clicked:', subjectTitle);
      openCourse(subjectTitle.toLowerCase(), subjectTitle);
    });
  });

  // Setup search functionality
  setupFilters();
});

/* Show tab content */
function showTab(tabName) {
  console.log('Showing tab:', tabName);
  document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
  const targetTab = document.getElementById('tab-' + tabName);
  if (targetTab) {
    targetTab.classList.remove('hidden');
  }
}

/* Open a course (subject) */
function openCourse(subjectId, subjectTitle) {
  console.log('Opening course:', subjectId, subjectTitle);
  
  // Store current subject globally
  currentSubjectId = subjectId;
  
  // Ensure completed weeks exists for this subject
  if (!user.completedWeeks[subjectId]) {
    user.completedWeeks[subjectId] = 0;
    localStorage.setItem('completedWeeks', JSON.stringify(user.completedWeeks));
  }

  // Set header
  document.getElementById('course-title').textContent = subjectTitle;
  document.getElementById('course-subtitle').textContent = `Program: ${user.simulatedProgram}`;

  // Hide subjects & show course view
  document.getElementById('subjects-view').classList.add('hidden');
  document.getElementById('course-view').classList.remove('hidden');

  // Reset to videos tab
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  const videosTab = document.querySelector('.tab[data-tab="videos"]');
  if (videosTab) {
    videosTab.classList.add('active');
  }
  showTab('videos');

  // Render course content
  renderCourseContent(subjectId);
}

/* Render the full course content */
function renderCourseContent(subjectId) {
  console.log('Rendering course content for:', subjectId);
  
  const videosList = document.getElementById('videos-list');
  const notesList = document.getElementById('notes-list');
  const extraList = document.getElementById('extra-list');
  const examsList = document.getElementById('exams-list');

  // Clear previous content
  if (videosList) videosList.innerHTML = '';
  if (notesList) notesList.innerHTML = '';
  if (extraList) extraList.innerHTML = '';
  if (examsList) examsList.innerHTML = '';

  const content = courseContent[subjectId];
  const completed = user.completedWeeks[subjectId] || 0;

  console.log('Completed weeks for', subjectId, ':', completed);

  if (!content || !content.weeks || content.weeks.length === 0) {
    const message = document.createElement('div');
    message.className = 'card';
    message.innerHTML = '<p>No course content available yet for this subject. Check back soon!</p>';
    if (videosList) videosList.appendChild(message);
    return;
  }

  // Render content for each week
  content.weeks.forEach(week => {
    const isLocked = week.week > (completed + 1);
    
    console.log(`Week ${week.week}: completed=${completed}, isLocked=${isLocked}`);

    // Videos tab content - NOW WITH ACTUAL VIDEO PLAYER
    if (videosList) {
      const videoCard = document.createElement('div');
      videoCard.className = `week-card ${isLocked ? 'locked' : ''}`;
      
      if (isLocked) {
        videoCard.innerHTML = `
          <div class="week-meta">
            <div class="week-title">Week ${week.week}: ${week.title}</div>
            <div class="week-desc">🔒 Complete previous weeks to unlock this video</div>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <div class="video-thumb" style="background: #ccc; color: #666;">
              🔒 LOCKED
            </div>
            <div style="font-size:13px;color:#999">Complete Week ${week.week - 1} first</div>
          </div>
        `;
      } else {
        videoCard.innerHTML = `
          <div class="week-meta">
            <div class="week-title">Week ${week.week}: ${week.title}</div>
            <div class="week-desc">${week.videoTitle || 'Video lesson'}</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px;width:100%;">
            <div class="video-container">
              <iframe 
                width="100%" 
                height="315" 
                src="https://www.youtube.com/embed/${week.videoId}" 
                title="${week.videoTitle || week.title}"
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
              </iframe>
            </div>
            <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;">
              <div style="font-size:14px;color:#666">Watch the video to complete this week</div>
              <button class="btn-primary mark-complete-btn" data-week="${week.week}">
                ✅ Mark as Completed
              </button>
            </div>
          </div>
        `;
      }
      videosList.appendChild(videoCard);
    }

    // Notes tab content
    if (notesList) {
      const noteCard = document.createElement('div');
      noteCard.className = `week-card ${isLocked ? 'locked' : ''}`;
      noteCard.innerHTML = `
        <div class="week-meta">
          <div class="week-title">Week ${week.week}: ${week.title}</div>
          <div class="week-desc">Notes: ${week.note}</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
          ${isLocked ? 
            '<div style="font-size:13px;color:#999">🔒 Complete the video first</div>' : 
            `<a class="btn-primary" href="#" onclick="downloadFile('${week.note}')">Download Notes</a>`
          }
        </div>
      `;
      notesList.appendChild(noteCard);
    }

    // Extra resources tab content
    if (extraList) {
      const extraCard = document.createElement('div');
      extraCard.className = 'week-card';
      extraCard.innerHTML = `
        <div class="week-meta">
          <div class="week-title">${week.extra.title}</div>
          <div class="week-desc">Additional learning materials</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
          <a class="btn-ghost" href="${week.extra.link}" target="_blank">Visit Resource</a>
        </div>
      `;
      extraList.appendChild(extraCard);
    }

    // Exams tab content
    if (examsList && week.exams && week.exams.length > 0) {
      week.exams.forEach(exam => {
        const examCard = document.createElement('div');
        examCard.className = 'week-card';
        examCard.innerHTML = `
          <div class="week-meta">
            <div class="week-title">${exam.year} Past Exam</div>
            <div class="week-desc">Official examination paper</div>
          </div>
          <div>
            <a class="btn-primary" href="#" onclick="downloadFile('${exam.file}')">Download Exam</a>
          </div>
        `;
        examsList.appendChild(examCard);
      });
    }
  });

  // Add event listeners to mark complete buttons AFTER they are created
  setTimeout(() => {
    document.querySelectorAll('.mark-complete-btn').forEach(button => {
      button.addEventListener('click', function() {
        const weekNumber = parseInt(this.getAttribute('data-week'));
        console.log('Mark complete button clicked for week:', weekNumber);
        simulateWatch(currentSubjectId, weekNumber);
      });
    });
  }, 100);

  // If no exams available, show message
  if (examsList && examsList.children.length === 0) {
    const noExams = document.createElement('div');
    noExams.className = 'card';
    noExams.innerHTML = '<p>No past exams available for this subject yet.</p>';
    examsList.appendChild(noExams);
  }
}

/* Simulate watching a video / completing a week */
function simulateWatch(subjectId, week) {
  console.log(`Simulating watch: subject=${subjectId}, week=${week}`);
  
  // Update completed weeks
  user.completedWeeks[subjectId] = Math.max(user.completedWeeks[subjectId] || 0, week);
  localStorage.setItem('completedWeeks', JSON.stringify(user.completedWeeks));
  
  console.log('Updated completed weeks:', user.completedWeeks[subjectId]);
  
  // Re-render course content to update locks
  renderCourseContent(subjectId);
  
  // Show success message
  const nextWeek = week + 1;
  const totalWeeks = courseContent[subjectId]?.weeks?.length || 0;
  
  if (nextWeek <= totalWeeks) {
    alert(`🎉 Great job! Week ${week} marked as completed. You can now access Week ${nextWeek}.`);
  } else {
    alert(`🎉 Congratulations! You have completed all ${totalWeeks} weeks of ${subjectId.charAt(0).toUpperCase() + subjectId.slice(1)}!`);
  }
}

/* Simulate file download */
function downloadFile(filename) {
  alert(`📥 Downloading: ${filename}\n\n(In a real application, this would download the actual file)`);
  return false; // Prevent default link behavior
}

/* Filters and search */
function setupFilters() {
  const search = document.getElementById('search-input');
  if (search) {
    search.addEventListener('input', function() {
      const query = this.value.trim().toLowerCase();
      filterSubjects(query);
    });
  }

  // Filter checkboxes
  const filterNotes = document.getElementById('filter-notes');
  const filterVideos = document.getElementById('filter-videos');
  const filterExams = document.getElementById('filter-exams');
  
  if (filterNotes) filterNotes.addEventListener('change', filterContent);
  if (filterVideos) filterVideos.addEventListener('change', filterContent);
  if (filterExams) filterExams.addEventListener('change', filterContent);
}

function filterSubjects(query) {
  const cards = document.querySelectorAll('#subjects-grid .subject-card');
  let visibleCount = 0;
  
  cards.forEach(card => {
    const title = card.querySelector('h3').textContent.toLowerCase();
    const desc = card.querySelector('p').textContent.toLowerCase();
    const show = title.includes(query) || desc.includes(query);
    
    card.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  // Show message if no results
  const grid = document.getElementById('subjects-grid');
  if (grid) {
    let noResults = grid.querySelector('.no-results');
    
    if (visibleCount === 0 && query !== '') {
      if (!noResults) {
        noResults = document.createElement('div');
        noResults.className = 'no-results card';
        noResults.innerHTML = `<p>No subjects found matching "${query}"</p>`;
        grid.appendChild(noResults);
      }
    } else if (noResults) {
      noResults.remove();
    }
  }
}

function filterContent() {
  // This would filter the displayed content based on checkbox selections
  console.log('Filters updated');
}