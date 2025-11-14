// js/resources.js
// Module-style script for the Resources page

// Simulated user profile (in real app, this comes from backend after login)
const user = {
  name: "Amina",
  // set this to "ABAC" or "Baccalaureat" to simulate different programs
  simulatedProgram: localStorage.getItem('simProgram') || 'Baccalaureat',
  // completed weeks per subject (simulate progress)
  // in real app, this would come from user progress in database
  completedWeeks: JSON.parse(localStorage.getItem('completedWeeks') || '{}')
};

// Example data structure of subjects and course content per program
const programs = {
  "Baccalaureat": {
    subjects: [
      { id: 'math', title: 'Mathematics', desc: 'Core topics & weekly lessons' },
      { id: 'physics', title: 'Physics', desc: 'Mechanics & more' },
      { id: 'english', title: 'English', desc: 'Grammar & essays' },
      { id: 'economics', title: 'Economics', desc: 'Basics & past papers' },
      { id: 'biology', title: 'Biology', desc: 'Cells to ecology' }
    ]
  },
  "ABAC": {
    subjects: [
      { id: 'math', title: 'Mathematics', desc: 'Core topics & weekly lessons' },
      { id: 'french', title: 'French', desc: 'Reading & comprehension' },
      { id: 'history', title: 'History', desc: 'Colonial and modern history' },
      { id: 'geography', title: 'Geography', desc: 'Maps & environment' }
    ]
  }
};

// Course content per subject (for demonstration)
const courseContent = {
  math: {
    weeks: [
      { week: 1, title: 'Introduction to Algebra', videoId: 'VIDEO-THUMB-1', note: 'Algebra_Basics.pdf', extra: { title: 'Algebra by Example', link: '#' }, exams: [{ year: 2023, file: 'BEPC_Math_2023.pdf' }] },
      { week: 2, title: 'Linear Equations', videoId: 'VIDEO-THUMB-2', note: 'Linear_Equations.pdf', extra: { title: 'Khan Academy Algebra', link: '#' }, exams: [{ year: 2022, file: 'BEPC_Math_2022.pdf' }] },
      { week: 3, title: 'Quadratic Equations', videoId: 'VIDEO-THUMB-3', note: 'Quadratic_Notes.pdf', extra: { title: 'Advanced Problems', link: '#' }, exams: [{ year: 2021, file: 'BEPC_Math_2021.pdf' }] },
      { week: 4, title: 'Polynomials', videoId: 'VIDEO-THUMB-4', note: 'Polynomials.pdf', extra: { title: 'Polynomials Book', link: '#' }, exams: [] }
    ]
  },
  physics: {
    weeks: [
      { week: 1, title: 'Kinematics', videoId: 'VIDEO-THUMB-A', note: 'Kinematics.pdf', extra: { title: 'Physics Textbook', link: '#' }, exams: [{ year: 2023, file: 'BEPC_Physics_2023.pdf' }] }
    ]
  }
  // add other subjects similarly
};

// Utility to ensure localStorage object exists
function ensureCompletedWeeksForSubject(subjectId) {
  if (!user.completedWeeks[subjectId]) {
    user.completedWeeks[subjectId] = 0; // 0 weeks completed by default
    localStorage.setItem('completedWeeks', JSON.stringify(user.completedWeeks));
  }
}

// Initialize UI
document.addEventListener('DOMContentLoaded', () => {
  // show name & program
  document.getElementById('user-name').innerText = user.name;
  document.getElementById('user-program').innerText = user.simulatedProgram;

  // render small subject list in sidebar
  renderSidebarSubjectList();

  // render main subjects grid
  renderSubjectsGrid();

  // attach search & filter handlers
  setupFilters();

  // back button handler
  document.getElementById('back-to-subjects').addEventListener('click', () => {
    document.getElementById('course-view').classList.add('hidden');
    document.getElementById('subjects-view').classList.remove('hidden');
  });

  // tabs
  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      const tabName = tab.dataset.tab;
      showTab(tabName);
    });
  });
});

/* RENDERERS */

function renderSidebarSubjectList() {
  const el = document.getElementById('subject-list-small');
  el.innerHTML = '';
  const subs = programs[user.simulatedProgram].subjects;
  subs.forEach(s => {
    const d = document.createElement('div');
    d.className = 'small-sub';
    d.innerHTML = `<button class="btn-ghost" style="width:100%; text-align:left; margin:6px 0;">${s.title}</button>`;
    d.querySelector('button').addEventListener('click', () => openCourse(s.id, s.title));
    el.appendChild(d);
  });
}

function renderSubjectsGrid() {
  const container = document.getElementById('subjects-grid');
  container.innerHTML = '';
  const subs = programs[user.simulatedProgram].subjects;
  subs.forEach(s => {
    const card = document.createElement('div');
    card.className = 'subject-card card';
    card.innerHTML = `
      <div>
        <h3>${s.title}</h3>
        <p>${s.desc}</p>
      </div>
      <div style="align-self:flex-end;">
        <button class="btn-primary">View Materials</button>
      </div>
    `;
    card.querySelector('.btn-primary').addEventListener('click', () => openCourse(s.id, s.title));
    container.appendChild(card);
  });
}

/* Open a course (subject) */
function openCourse(subjectId, subjectTitle) {
  // ensure completed weeks exists
  ensureCompletedWeeksForSubject(subjectId);

  // set header
  document.getElementById('course-title').innerText = subjectTitle;
  document.getElementById('course-subtitle').innerText = `Program: ${user.simulatedProgram}`;

  // hide subjects & show course view
  document.getElementById('subjects-view').classList.add('hidden');
  document.getElementById('course-view').classList.remove('hidden');

  // by default show videos tab
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelector('.tab[data-tab="videos"]').classList.add('active');
  showTab('videos');

  // render contents for given subject
  renderCourseContent(subjectId);
}

/* Show tab content */
function showTab(tabName) {
  document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
  document.getElementById('tab-' + tabName).classList.remove('hidden');
}

/* Render the full course content */
function renderCourseContent(subjectId) {
  // if courseContent doesn't have subjectId, show placeholder
  const videosList = document.getElementById('videos-list');
  const notesList = document.getElementById('notes-list');
  const extraList = document.getElementById('extra-list');
  const examsList = document.getElementById('exams-list');

  videosList.innerHTML = '';
  notesList.innerHTML = '';
  extraList.innerHTML = '';
  examsList.innerHTML = '';

  const content = courseContent[subjectId];
  if (!content || !content.weeks) {
    const note = document.createElement('div');
    note.className = 'card';
    note.innerText = 'No course content available yet for this subject.';
    videosList.appendChild(note);
    return;
  }

  const completed = user.completedWeeks[subjectId] || 0;

  content.weeks.forEach(w => {
    const isLocked = w.week > completed + 1; // students must complete weeks in order; completed weeks unlock next
    const weekCardForVideos = document.createElement('div');
    weekCardForVideos.className = 'week-card ' + (isLocked ? 'locked' : '');
    weekCardForVideos.innerHTML = `
      <div class="week-meta">
        <div class="week-title">Week ${w.week}: ${w.title}</div>
        <div class="week-desc">${w.description || ''}</div>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        <div class="video-thumb">▶</div>
        ${ isLocked ? `<div style="font-size:13px;color:#999">🔒 Locked</div>` : `<button class="btn-primary" onclick="simulateWatch('${subjectId}', ${w.week})">Watch</button>` }
      </div>
    `;
    videosList.appendChild(weekCardForVideos);

    // Notes entry
    const noteCard = document.createElement('div');
    noteCard.className = 'week-card ' + (isLocked ? 'locked' : '');
    noteCard.innerHTML = `
      <div class="week-meta">
        <div class="week-title">Week ${w.week}: ${w.title}</div>
        <div class="week-desc">Notes: ${w.note || 'No notes yet'}</div>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        ${ isLocked ? `<div style="font-size:13px;color:#999">🔒 Locked</div>` : `<a class="btn-primary" href="./assets/docs/${w.note}" download>Download</a>` }
      </div>
    `;
    notesList.appendChild(noteCard);

    // Extra resources
    const extraCard = document.createElement('div');
    extraCard.className = 'week-card';
    extraCard.innerHTML = `
      <div class="week-meta">
        <div class="week-title">${w.extra.title}</div>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        <a class="btn-ghost" href="${w.extra.link}" target="_blank">Visit</a>
      </div>
    `;
    extraList.appendChild(extraCard);

    // Past exams
    if (w.exams && w.exams.length) {
      w.exams.forEach(ex => {
        const exCard = document.createElement('div');
        exCard.className = 'week-card';
        exCard.innerHTML = `
          <div class="week-meta">
            <div class="week-title">${ex.year} – Past Exam</div>
          </div>
          <div>
            <a class="btn-primary" href="./assets/exams/${ex.file}" download>Download</a>
          </div>
        `;
        examsList.appendChild(exCard);
      });
    }
  });

  // Add debug controls to simulate completing weeks (only for demo)
  addDemoControls(subjectId);
}

/* Simulate watching a video / completing a week */
window.simulateWatch = function(subjectId, week) {
  // mark weeks up to 'week' as completed
  user.completedWeeks[subjectId] = Math.max(user.completedWeeks[subjectId] || 0, week);
  localStorage.setItem('completedWeeks', JSON.stringify(user.completedWeeks));
  // re-render course content so locks update
  renderCourseContent(subjectId);
  alert(`Marked Week ${week} as completed for demo purposes.`);
};

/* Filters and search */
function setupFilters() {
  const search = document.getElementById('search-input');
  search.addEventListener('input', () => {
    const q = search.value.trim().toLowerCase();
    filterSubjects(q);
  });

  document.getElementById('filter-notes').addEventListener('change', () => { /* future: filter card badges */ });
  document.getElementById('filter-videos').addEventListener('change', () => { /* future */ });
  document.getElementById('filter-exams').addEventListener('change', () => { /* future */ });
}

function filterSubjects(query) {
  const cards = document.querySelectorAll('#subjects-grid .subject-card');
  cards.forEach(card => {
    const title = card.querySelector('h3').innerText.toLowerCase();
    const desc = card.querySelector('p').innerText.toLowerCase();
    const show = title.includes(query) || desc.includes(query);
    card.style.display = show ? '' : 'none';
  });
}
