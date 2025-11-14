
// Get user progress from localStorage (same as in resources page)
const user = {
name: "Chichibest",
simulatedProgram: localStorage.getItem('simProgram') || 'Baccalaureat',
completedWeeks: JSON.parse(localStorage.getItem('completedWeeks') || '{}'),
quizScores: JSON.parse(localStorage.getItem('quizScores') || '{}')
};

// Quiz data organized by subject and week
const quizData = {
mathematics: {
name: "Mathematics",
weeks: {
1: {
title: "Introduction to Algebra",
questions: [
    {
    question: "What is 5 × 7?",
    options: ["30", "35", "40", "45"],
    correct: 1
    },
    {
    question: "What is the value of 3² + 4²?",
    options: ["7", "12", "25", "49"],
    correct: 2
    },
    {
    question: "Solve for x: 2x + 5 = 15",
    options: ["x = 5", "x = 10", "x = 7.5", "x = 4"],
    correct: 0
    },
    {
    question: "What is the square root of 64?",
    options: ["6", "7", "8", "9"],
    correct: 2
    },
    {
    question: "Simplify: 3(2 + 4) - 5",
    options: ["15", "13", "17", "19"],
    correct: 1
    }
]
},
2: {
title: "Linear Equations",
questions: [
    {
    question: "What is the slope of the line y = 2x + 3?",
    options: ["2", "3", "1", "0"],
    correct: 0
    },
    {
    question: "Solve the equation: 3x - 7 = 14",
    options: ["x = 6", "x = 7", "x = 8", "x = 9"],
    correct: 1
    },
    {
    question: "What is the y-intercept of y = -2x + 5?",
    options: ["-2", "2", "5", "-5"],
    correct: 2
    },
    {
    question: "Which point lies on the line y = x + 2?",
    options: ["(1, 1)", "(2, 4)", "(3, 5)", "(0, 2)"],
    correct: 2
    },
    {
    question: "Solve the system: 2x + y = 7, x - y = 2",
    options: ["(3, 1)", "(2, 3)", "(4, -1)", "(1, 5)"],
    correct: 0
    }
]
},
3: {
title: "Quadratic Equations",
questions: [
    {
    question: "What is the solution to x² - 4 = 0?",
    options: ["x = 2", "x = -2", "x = ±2", "x = 4"],
    correct: 2
    },
    {
    question: "Factor: x² + 5x + 6",
    options: ["(x+2)(x+3)", "(x+1)(x+6)", "(x+2)(x+4)", "(x+3)(x+3)"],
    correct: 0
    },
    {
    question: "What is the vertex of y = x² - 4x + 3?",
    options: ["(2, -1)", "(1, 0)", "(3, 0)", "(4, 3)"],
    correct: 0
    },
    {
    question: "Solve using quadratic formula: x² - 6x + 8 = 0",
    options: ["x = 2, 4", "x = 1, 8", "x = 3, 3", "x = -2, -4"],
    correct: 0
    },
    {
    question: "What is the discriminant of x² + 3x + 2?",
    options: ["1", "4", "9", "16"],
    correct: 0
    }
]
}
}
},
physics: {
name: "Physics",
weeks: {
1: {
title: "Kinematics",
questions: [
    {
    question: "What is the formula for velocity?",
    options: ["distance/time", "mass × acceleration", "force/area", "work/time"],
    correct: 0
    },
    {
    question: "What does the slope of a distance-time graph represent?",
    options: ["Acceleration", "Velocity", "Force", "Energy"],
    correct: 1
    },
    {
    question: "A car accelerates from 0 to 60 m/s in 5 seconds. What is its acceleration?",
    options: ["10 m/s²", "12 m/s²", "15 m/s²", "20 m/s²"],
    correct: 1
    },
    {
    question: "What is the SI unit of acceleration?",
    options: ["m/s", "m/s²", "N", "J"],
    correct: 1
    },
    {
    question: "An object in free fall near Earth's surface accelerates at:",
    options: ["5 m/s²", "9.8 m/s²", "15 m/s²", "20 m/s²"],
    correct: 1
    }
]
}
}
},
english: {
name: "English",
weeks: {
1: {
title: "Grammar Basics",
questions: [
    {
    question: "Which of these is a noun?",
    options: ["Run", "Beautiful", "Happiness", "Quickly"],
    correct: 2
    },
    {
    question: "What is the past tense of 'go'?",
    options: ["Goed", "Went", "Gone", "Going"],
    correct: 1
    },
    {
    question: "Which word is an adjective?",
    options: ["Happily", "Happiness", "Happy", "Happen"],
    correct: 2
    },
    {
    question: "What is a synonym for 'big'?",
    options: ["Small", "Large", "Tiny", "Little"],
    correct: 1
    },
    {
    question: "Which sentence is correct?",
    options: [
        "She don't like apples.",
        "She doesn't like apples.",
        "She not like apples.",
        "She doesn't likes apples."
    ],
    correct: 1
    }
]
}
}
},
economics: {
name: "Economics",
weeks: {
1: {
title: "Supply and Demand",
questions: [
    {
    question: "What does GDP stand for?",
    options: ["Gross Domestic Product", "General Domestic Price", "Gross Demand Product", "General Demand Price"],
    correct: 0
    },
    {
    question: "What is the basic economic problem?",
    options: ["Unemployment", "Inflation", "Scarcity", "Taxation"],
    correct: 2
    },
    {
    question: "Which of these is a factor of production?",
    options: ["Money", "Land", "Taxes", "Interest"],
    correct: 1
    },
    {
    question: "What is inflation?",
    options: ["Increase in money supply", "Decrease in prices", "Increase in prices", "Decrease in employment"],
    correct: 2
    },
    {
    question: "What is the opportunity cost of a decision?",
    options: ["The money spent", "The next best alternative", "The time invested", "The risk involved"],
    correct: 1
    }
]
}
}
},
biology: {
name: "Biology",
weeks: {
1: {
title: "Cell Structure",
questions: [
    {
    question: "What is the powerhouse of the cell?",
    options: ["Nucleus", "Mitochondria", "Ribosome", "Cell membrane"],
    correct: 1
    },
    {
    question: "Which organelle contains DNA?",
    options: ["Mitochondria", "Ribosome", "Nucleus", "Golgi apparatus"],
    correct: 2
    },
    {
    question: "What is the function of ribosomes?",
    options: ["Energy production", "Protein synthesis", "Cellular transport", "Waste removal"],
    correct: 1
    },
    {
    question: "Which of these is found in plant cells but not animal cells?",
    options: ["Mitochondria", "Cell wall", "Nucleus", "Cell membrane"],
    correct: 1
    },
    {
    question: "What is the function of chloroplasts?",
    options: ["Cellular respiration", "Protein synthesis", "Photosynthesis", "DNA replication"],
    correct: 2
    }
]
}
}
}
};

// Quiz state
let currentSubject = null;
let currentWeek = null;
let currentQuestionIndex = 0;
let userAnswers = [];
let score = 0;

// DOM elements
const quizIntro = document.getElementById('quiz-intro');
const quizArea = document.getElementById('quiz-area');
const resultsArea = document.getElementById('results-area');
const subjectSelect = document.getElementById('subject-select');
const weekSelection = document.getElementById('week-selection');
const currentWeekInfo = document.getElementById('current-week-info');
const questionNumber = document.getElementById('question-number');
const subjectName = document.getElementById('subject-name');
const questionText = document.getElementById('question-text');
const optionsContainer = document.getElementById('options-container');
const prevQuestionBtn = document.getElementById('prev-question');
const nextQuestionBtn = document.getElementById('next-question');
const progressBar = document.getElementById('progress');
const scoreElement = document.getElementById('score');
const feedbackElement = document.getElementById('feedback');
const retakeQuizBtn = document.getElementById('retake-quiz');
const goResourcesBtn = document.getElementById('go-resources');

// Event listeners
subjectSelect.addEventListener('change', loadWeeks);
retakeQuizBtn.addEventListener('click', retakeQuiz);
goResourcesBtn.addEventListener('click', () => {
window.location.href = 'resources.html';
});

// Load available weeks for selected subject
function loadWeeks() {
const selectedSubject = subjectSelect.value;

if (!selectedSubject) {
weekSelection.innerHTML = '';
return;
}

currentSubject = quizData[selectedSubject];
const completedWeeks = user.completedWeeks[selectedSubject] || 0;

weekSelection.innerHTML = '';

// Create week cards
Object.keys(currentSubject.weeks).forEach(weekNum => {
const week = currentSubject.weeks[weekNum];
const weekCard = document.createElement('div');
weekCard.className = 'week-card';

const isCompleted = parseInt(weekNum) <= completedWeeks;
const isAvailable = parseInt(weekNum) <= completedWeeks + 1;
const hasQuizScore = user.quizScores[`${selectedSubject}-week-${weekNum}`];

if (isAvailable) {
weekCard.classList.add('available');
weekCard.addEventListener('click', () => startQuiz(selectedSubject, parseInt(weekNum)));
} else {
weekCard.classList.add('locked');
}

if (hasQuizScore) {
weekCard.classList.add('completed');
}

weekCard.innerHTML = `
<div class="week-number">Week ${weekNum}</div>
<div class="week-title">${week.title}</div>
<div class="week-status ${isAvailable ? (hasQuizScore ? 'status-completed' : 'status-available') : 'status-locked'}">
${hasQuizScore ? `Score: ${user.quizScores[`${selectedSubject}-week-${weekNum}`]}` : (isAvailable ? 'Available' : 'Locked')}
</div>
${!isAvailable ? '<div style="margin-top: 8px; font-size: 11px; color: #999;">Complete Week ' + (parseInt(weekNum) - 1) + ' lecture first</div>' : ''}
`;

weekSelection.appendChild(weekCard);
});
}

// Start the quiz for a specific week
function startQuiz(subject, week) {
currentSubject = quizData[subject];
currentWeek = week;
currentQuestionIndex = 0;
userAnswers = new Array(currentSubject.weeks[week].questions.length).fill(null);

quizIntro.style.display = 'none';
quizArea.style.display = 'block';

// Set week info
currentWeekInfo.textContent = `Week ${week}: ${currentSubject.weeks[week].title}`;

loadQuestion();
}

// Load the current question
function loadQuestion() {
const question = currentSubject.weeks[currentWeek].questions[currentQuestionIndex];

// Update question info
questionNumber.textContent = `Question ${currentQuestionIndex + 1} of ${currentSubject.weeks[currentWeek].questions.length}`;
subjectName.textContent = currentSubject.name;
questionText.textContent = question.question;

// Update progress bar
const progress = ((currentQuestionIndex + 1) / currentSubject.weeks[currentWeek].questions.length) * 100;
progressBar.style.width = `${progress}%`;

// Clear and populate options
optionsContainer.innerHTML = '';
question.options.forEach((option, index) => {
const optionElement = document.createElement('div');
optionElement.className = 'option';

if (userAnswers[currentQuestionIndex] === index) {
optionElement.classList.add('selected');
}

optionElement.textContent = option;
optionElement.addEventListener('click', () => selectOption(index));
optionsContainer.appendChild(optionElement);
});

// Update navigation buttons
prevQuestionBtn.style.display = currentQuestionIndex === 0 ? 'none' : 'block';

if (currentQuestionIndex === currentSubject.weeks[currentWeek].questions.length - 1) {
nextQuestionBtn.textContent = 'Finish Quiz';
} else {
nextQuestionBtn.textContent = 'Next';
}

// Add event listeners for navigation buttons
prevQuestionBtn.onclick = prevQuestion;
nextQuestionBtn.onclick = nextQuestion;
}

// Select an option
function selectOption(optionIndex) {
// Remove selected class from all options
document.querySelectorAll('.option').forEach(option => {
option.classList.remove('selected');
});

// Add selected class to clicked option
document.querySelectorAll('.option')[optionIndex].classList.add('selected');

// Save the answer
userAnswers[currentQuestionIndex] = optionIndex;
}

// Go to previous question
function prevQuestion() {
if (currentQuestionIndex > 0) {
currentQuestionIndex--;
loadQuestion();
}
}

// Go to next question or finish quiz
function nextQuestion() {
if (userAnswers[currentQuestionIndex] === null) {
alert('Please select an answer before proceeding');
return;
}

if (currentQuestionIndex < currentSubject.weeks[currentWeek].questions.length - 1) {
currentQuestionIndex++;
loadQuestion();
} else {
finishQuiz();
}
}

// Finish the quiz and show results
function finishQuiz() {
// Calculate score
score = 0;
currentSubject.weeks[currentWeek].questions.forEach((question, index) => {
if (userAnswers[index] === question.correct) {
score++;
}
});

// Save quiz score
const quizKey = `${subjectSelect.value}-week-${currentWeek}`;
user.quizScores[quizKey] = `${score}/${currentSubject.weeks[currentWeek].questions.length}`;
localStorage.setItem('quizScores', JSON.stringify(user.quizScores));

// Display results
quizArea.style.display = 'none';
resultsArea.style.display = 'block';

scoreElement.textContent = `${score}/${currentSubject.weeks[currentWeek].questions.length}`;

const percentage = (score / currentSubject.weeks[currentWeek].questions.length) * 100;
if (percentage >= 80) {
feedbackElement.textContent = 'Excellent work!';
} else if (percentage >= 60) {
feedbackElement.textContent = 'Good job! Keep practicing!';
} else {
feedbackElement.textContent = 'Review the material and try again!';
}
}

// Retake the quiz
function retakeQuiz() {
resultsArea.style.display = 'none';
quizIntro.style.display = 'block';
loadWeeks(); // Reload weeks to show updated scores
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
loadWeeks();
});
