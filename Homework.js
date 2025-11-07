// Homework.js - UPDATED VERSION WITH DICTIONARY API
console.log("Homework.js loaded successfully!");

// Initialize user data structure first
function initializeUserData() {
    console.log("Initializing user data...");
    
    // Initialize completedWeeks if not exists
    if (!localStorage.getItem('completedWeeks')) {
        console.log("Creating completedWeeks in localStorage");
        localStorage.setItem('completedWeeks', JSON.stringify({
            mathematics: 0,
            physics: 0, 
            english: 0,
            economics: 0,
            biology: 0
        }));
    }
    
    // Initialize homeworkScores if not exists
    if (!localStorage.getItem('homeworkScores')) {
        console.log("Creating homeworkScores in localStorage");
        localStorage.setItem('homeworkScores', JSON.stringify({
            mathematics: {},
            physics: {},
            english: {},
            economics: {},
            biology: {}
        }));
    }
    
    // Initialize homeworkSubmissions if not exists
    if (!localStorage.getItem('homeworkSubmissions')) {
        console.log("Creating homeworkSubmissions in localStorage");
        localStorage.setItem('homeworkSubmissions', JSON.stringify({}));
    }
}

// Get user progress from localStorage with proper initialization
function getUserData() {
    try {
        const completedWeeks = JSON.parse(localStorage.getItem('completedWeeks') || '{"mathematics":0,"physics":0,"english":0,"economics":0,"biology":0}');
        const homeworkScores = JSON.parse(localStorage.getItem('homeworkScores') || '{"mathematics":{},"physics":{},"english":{},"economics":{},"biology":{}}');
        const homeworkSubmissions = JSON.parse(localStorage.getItem('homeworkSubmissions') || '{}');
        
        console.log("User data loaded:", {
            completedWeeks,
            homeworkScores,
            homeworkSubmissions
        });
        
        return {
            name: "Chichibest",
            simulatedProgram: localStorage.getItem('simProgram') || 'Baccalaureat',
            completedWeeks: completedWeeks,
            homeworkScores: homeworkScores,
            homeworkSubmissions: homeworkSubmissions
        };
    } catch (error) {
        console.error("Error loading user data:", error);
        // Return safe defaults
        return {
            name: "Chichibest",
            simulatedProgram: 'Baccalaureat',
            completedWeeks: {mathematics:0, physics:0, english:0, economics:0, biology:0},
            homeworkScores: {mathematics:{}, physics:{}, english:{}, economics:{}, biology:{}},
            homeworkSubmissions: {}
        };
    }
}

let user = getUserData();

// Homework data organized by subject and week
const homeworkData = {
    mathematics: {
        name: "Mathematics",
        weeks: {
            1: {
                title: "Introduction to Algebra",
                description: "This exercise covers basic algebraic operations, solving linear equations, and simplifying expressions.",
                exerciseFile: "algebra_week1_exercise.pdf",
                questions: 5,
                estimatedTime: "45 minutes"
            },
            2: {
                title: "Linear Equations",
                description: "Practice solving systems of linear equations and understanding slope-intercept form.",
                exerciseFile: "algebra_week2_exercise.pdf",
                questions: 6,
                estimatedTime: "60 minutes"
            },
            3: {
                title: "Quadratic Equations",
                description: "Work with quadratic functions, factoring, and the quadratic formula.",
                exerciseFile: "algebra_week3_exercise.pdf",
                questions: 7,
                estimatedTime: "75 minutes"
            }
        }
    },
    physics: {
        name: "Physics",
        weeks: {
            1: {
                title: "Kinematics",
                description: "Solve problems related to motion, velocity, acceleration, and displacement.",
                exerciseFile: "physics_week1_exercise.pdf",
                questions: 4,
                estimatedTime: "50 minutes"
            }
        }
    },
    english: {
        name: "English",
        weeks: {
            1: {
                title: "Grammar Basics",
                description: "Practice identifying parts of speech and correcting grammatical errors.",
                exerciseFile: "english_week1_exercise.pdf",
                questions: 8,
                estimatedTime: "40 minutes"
            },
            2: {
                title: "Vocabulary Building",
                description: "Expand your vocabulary with common academic words and their usage.",
                exerciseFile: "english_week2_exercise.pdf",
                questions: 10,
                estimatedTime: "50 minutes"
            },
            3: {
                title: "Essay Writing",
                description: "Learn to structure essays and improve writing style.",
                exerciseFile: "english_week3_exercise.pdf",
                questions: 3,
                estimatedTime: "60 minutes"
            }
        }
    },
    economics: {
        name: "Economics",
        weeks: {
            1: {
                title: "Supply and Demand",
                description: "Analyze market equilibrium and shifts in supply and demand curves.",
                exerciseFile: "economics_week1_exercise.pdf",
                questions: 5,
                estimatedTime: "55 minutes"
            }
        }
    },
    biology: {
        name: "Biology",
        weeks: {
            1: {
                title: "Cell Structure",
                description: "Identify cell organelles and understand their functions through diagrams and questions.",
                exerciseFile: "biology_week1_exercise.pdf",
                questions: 6,
                estimatedTime: "50 minutes"
            }
        }
    }
};

console.log("Homework data structure loaded successfully");

// Current state
let currentSubject = null;
let currentWeek = null;
let selectedFile = null;

// DOM elements - with null checks
function getDOMElements() {
    const elements = {
        homeworkIntro: document.getElementById('homework-intro'),
        homeworkArea: document.getElementById('homework-area'),
        subjectSelect: document.getElementById('subject-select'),
        weekSelection: document.getElementById('week-selection'),
        homeworkTitle: document.getElementById('homework-title'),
        backToSubjects: document.getElementById('back-to-subjects'),
        exerciseDescription: document.getElementById('exercise-description'),
        downloadExercise: document.getElementById('download-exercise'),
        uploadArea: document.getElementById('upload-area'),
        fileInput: document.getElementById('file-input'),
        browseFiles: document.getElementById('browse-files'),
        fileInfo: document.getElementById('file-info'),
        submitHomework: document.getElementById('submit-homework'),
        submissionStatus: document.getElementById('submission-status')
    };
    
    // Check if all elements are found
    for (const [key, element] of Object.entries(elements)) {
        if (!element) {
            console.error(`DOM element not found: ${key}`);
        }
    }
    
    return elements;
}

let elements = {};

// Event listeners setup
function setupEventListeners() {
    console.log("Setting up event listeners...");
    
    if (elements.subjectSelect) {
        elements.subjectSelect.addEventListener('change', loadWeeks);
    } else {
        console.error("subjectSelect element not found!");
    }
    
    if (elements.backToSubjects) {
        elements.backToSubjects.addEventListener('click', showSubjectSelection);
    }
    
    if (elements.downloadExercise) {
        elements.downloadExercise.addEventListener('click', downloadExerciseFile);
    }
    
    if (elements.browseFiles && elements.fileInput) {
        elements.browseFiles.addEventListener('click', () => elements.fileInput.click());
    }
    
    if (elements.fileInput) {
        elements.fileInput.addEventListener('change', handleFileSelect);
    }
    
    if (elements.submitHomework) {
        elements.submitHomework.addEventListener('click', submitForReview);
    }
    
    // Drag and drop functionality
    if (elements.uploadArea) {
        elements.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            elements.uploadArea.classList.add('dragover');
        });

        elements.uploadArea.addEventListener('dragleave', () => {
            elements.uploadArea.classList.remove('dragover');
        });

        elements.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            elements.uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });
    }
}

// Load available weeks for selected subject
function loadWeeks() {
    console.log("loadWeeks called with subject:", elements.subjectSelect?.value);
    
    const selectedSubject = elements.subjectSelect?.value;
    
    if (!selectedSubject || !elements.weekSelection) {
        if (elements.weekSelection) {
            elements.weekSelection.innerHTML = '';
        }
        return;
    }
    
    currentSubject = homeworkData[selectedSubject];
    console.log("Current subject data:", currentSubject);
    
    // VALIDATION: Check if subject data exists
    if (!currentSubject) {
        console.error('Subject data not found for:', selectedSubject);
        if (elements.weekSelection) {
            elements.weekSelection.innerHTML = '<div class="error-message">Subject data not available. Please select another subject.</div>';
        }
        return;
    }
    
    if (!currentSubject.weeks) {
        console.error('Weeks data not found for subject:', selectedSubject);
        if (elements.weekSelection) {
            elements.weekSelection.innerHTML = '<div class="error-message">No weeks available for this subject.</div>';
        }
        return;
    }
    
    // Ensure the user data structure exists for this subject
    if (!user.completedWeeks[selectedSubject] && user.completedWeeks[selectedSubject] !== 0) {
        user.completedWeeks[selectedSubject] = 0;
    }
    if (!user.homeworkScores[selectedSubject]) {
        user.homeworkScores[selectedSubject] = {};
    }
    
    const completedWeeks = user.completedWeeks[selectedSubject] || 0;
    const homeworkScores = user.homeworkScores[selectedSubject] || {};
    
    console.log("User progress for", selectedSubject, {
        completedWeeks,
        homeworkScores
    });
    
    elements.weekSelection.innerHTML = '';
    
    // Create week cards
    Object.keys(currentSubject.weeks).forEach(weekNum => {
        const week = currentSubject.weeks[weekNum];
        const weekCard = document.createElement('div');
        weekCard.className = 'week-card';
        
        const weekInt = parseInt(weekNum);
        const isLectureCompleted = weekInt <= completedWeeks;
        const prevWeekCompleted = weekInt === 1 || (homeworkScores[weekInt - 1] >= 12);
        const isAvailable = isLectureCompleted && (weekInt === 1 || prevWeekCompleted);
        const currentScore = homeworkScores[weekInt];
        const isCompleted = currentScore >= 12;
        const isPending = currentScore !== undefined && currentScore < 12;
        
        if (isAvailable) {
            weekCard.classList.add('available');
            weekCard.addEventListener('click', () => openHomework(selectedSubject, weekInt));
        } else {
            weekCard.classList.add('locked');
        }
        
        if (isCompleted) {
            weekCard.classList.add('completed');
        } else if (isPending) {
            weekCard.classList.add('pending');
        }
        
        let statusText = 'Available';
        let statusClass = 'status-available';
        
        if (!isAvailable) {
            if (!isLectureCompleted) {
                statusText = 'Complete Lecture First';
            } else if (!prevWeekCompleted) {
                statusText = 'Pass Week ' + (weekInt - 1) + ' First';
            } else {
                statusText = 'Locked';
            }
            statusClass = 'status-locked';
        } else if (isCompleted) {
            statusText = 'Completed';
            statusClass = 'status-completed';
        } else if (isPending) {
            statusText = 'Needs Resubmission';
            statusClass = 'status-pending';
        }
        
        weekCard.innerHTML = `
            <div class="week-number">Week ${weekNum}</div>
            <div class="week-title">${week.title}</div>
            <div class="week-status ${statusClass}">${statusText}</div>
            ${currentScore !== undefined ? `
                <div class="week-score ${currentScore >= 12 ? 'score-pass' : 'score-fail'}">
                    Score: ${currentScore}/20
                </div>
            ` : ''}
            ${!isAvailable ? `
                <div style="margin-top: 8px; font-size: 11px; color: #999;">
                    ${!isLectureCompleted ? 'Complete Week ' + weekInt + ' lecture first' : 
                      !prevWeekCompleted ? 'Pass Week ' + (weekInt - 1) + ' homework first' : 'Not available'}
                </div>
            ` : ''}
        `;
        
        elements.weekSelection.appendChild(weekCard);
    });
    
    console.log("Week cards created successfully");
}

// Open homework for a specific week
function openHomework(subject, week) {
    console.log("Opening homework for:", subject, "week:", week);
    
    currentSubject = homeworkData[subject];
    
    // VALIDATION: Check if homework data exists
    if (!currentSubject) {
        console.error('Subject not found:', subject);
        alert('Subject not found. Please try another subject.');
        return;
    }
    
    if (!currentSubject.weeks) {
        console.error('Weeks data not found for subject:', subject);
        alert('No weeks available for this subject.');
        return;
    }
    
    if (!currentSubject.weeks[week]) {
        console.error('Week data not found for:', subject, 'week:', week);
        alert('Homework data not available for this week. Please try another week.');
        return;
    }
    
    currentWeek = week;
    const weekData = currentSubject.weeks[week];
    
    console.log("Opening week data:", weekData);
    
    if (elements.homeworkIntro) elements.homeworkIntro.style.display = 'none';
    if (elements.homeworkArea) elements.homeworkArea.style.display = 'block';
    
    // Set homework info
    if (elements.homeworkTitle) {
        elements.homeworkTitle.textContent = `Week ${week}: ${weekData.title}`;
    }
    
    if (elements.exerciseDescription) {
        elements.exerciseDescription.innerHTML = `
            <p>${weekData.description}</p>
            <p><strong>Questions:</strong> ${weekData.questions}</p>
            <p><strong>Estimated Time:</strong> ${weekData.estimatedTime}</p>
        `;
    }
    
    // Reset submission area
    resetSubmissionArea();
    
    // Check if there's a previous submission
    const submissionKey = `${subject}-week-${week}`;
    if (user.homeworkSubmissions[submissionKey]) {
        showPreviousSubmission(user.homeworkSubmissions[submissionKey]);
    }
}

// Reset submission area to initial state
function resetSubmissionArea() {
    if (elements.fileInfo) elements.fileInfo.textContent = '';
    if (elements.submitHomework) elements.submitHomework.style.display = 'none';
    if (elements.submissionStatus) elements.submissionStatus.style.display = 'none';
    selectedFile = null;
    if (elements.fileInput) elements.fileInput.value = '';
}

// Show previous submission results
function showPreviousSubmission(submission) {
    if (!elements.submissionStatus) return;
    
    elements.submissionStatus.style.display = 'block';
    elements.submissionStatus.className = 'submission-status status-graded';
    
    const isPass = submission.score >= 12;
    
    elements.submissionStatus.innerHTML = `
        <div class="grade-display ${isPass ? 'grade-pass' : 'grade-fail'}">
            ${submission.score}/20
        </div>
        <div style="text-align: center; margin-bottom: 15px;">
            ${isPass ? '✅ Congratulations! You passed!' : '❌ You need to resubmit (minimum 12/20 required)'}
        </div>
        <div class="feedback-section">
            <div class="feedback-title">Grammar & Vocabulary Feedback:</div>
            <div class="feedback-content">${submission.feedback}</div>
        </div>
        ${!isPass ? `
            <button class="btn-resubmit" id="resubmit-btn">Resubmit Homework</button>
        ` : ''}
    `;
    
    if (!isPass) {
        setTimeout(() => {
            const resubmitBtn = document.getElementById('resubmit-btn');
            if (resubmitBtn) {
                resubmitBtn.addEventListener('click', () => {
                    elements.submissionStatus.style.display = 'none';
                });
            }
        }, 100);
    }
}

// Go back to subject selection
function showSubjectSelection() {
    if (elements.homeworkArea) elements.homeworkArea.style.display = 'none';
    if (elements.homeworkIntro) elements.homeworkIntro.style.display = 'block';
    loadWeeks(); // Reload to show updated status
}

// Download exercise file
function downloadExerciseFile() {
    if (!currentSubject || !currentWeek) {
        alert('Please select a subject and week first.');
        return;
    }
    
    const weekData = currentSubject.weeks[currentWeek];
    alert(`Downloading: ${weekData.exerciseFile}\n\n(In a real application, this would download the actual PDF file)`);
    
    // Simulate download success
    console.log(`Downloaded exercise: ${weekData.exerciseFile}`);
}

// Handle file selection
function handleFileSelect(e) {
    if (e.target.files.length > 0) {
        handleFile(e.target.files[0]);
    }
}

// Handle selected file
function handleFile(file) {
    // Check file type
    const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'text/plain'];
    if (!validTypes.includes(file.type)) {
        alert('Please upload a PDF, image file (JPEG, PNG), or text file');
        return;
    }
    
    // Check file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        alert('File size must be less than 10MB');
        return;
    }
    
    selectedFile = file;
    if (elements.fileInfo) {
        elements.fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
    }
    if (elements.submitHomework) {
        elements.submitHomework.style.display = 'block';
    }
}

// Submit homework for grammar and vocabulary review using Dictionary API
async function submitForReview() {
    if (!selectedFile) {
        alert('Please select a file first');
        return;
    }

    if (!currentSubject || !currentWeek) {
        alert('Please select a subject and week first.');
        return;
    }

    // Show pending status
    if (elements.submissionStatus) {
        elements.submissionStatus.style.display = 'block';
        elements.submissionStatus.className = 'submission-status status-pending';
        elements.submissionStatus.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 24px; margin-bottom: 10px;">⏳</div>
                <div>Analyzing your grammar and vocabulary...</div>
                <div style="font-size: 14px; margin-top: 10px; color: #666;">Please wait a few seconds...</div>
            </div>
        `;
    }

    try {
        // Convert uploaded file into text
        const extractedText = await extractTextFromFile(selectedFile);
        if (!extractedText) {
            throw new Error("Could not read file content.");
        }

        // For English subjects, use dictionary API for vocabulary analysis
        let grammarFeedback = "";
        if (currentSubject.name.toLowerCase() === "english") {
            grammarFeedback = await analyzeWithDictionaryAPI(extractedText);
        } else {
            // For other subjects, provide general feedback
            grammarFeedback = await getGeneralFeedback(extractedText, currentSubject.name, currentWeek);
        }

        // Calculate score based on grammar and vocabulary quality
        const score = calculateGrammarScore(grammarFeedback, extractedText);

        // Save submission
        const submissionKey = `${elements.subjectSelect.value}-week-${currentWeek}`;
        user.homeworkSubmissions[submissionKey] = {
            score: score,
            feedback: grammarFeedback,
            timestamp: new Date().toISOString(),
            subject: currentSubject.name,
            week: currentWeek
        };

        if (!user.homeworkScores[elements.subjectSelect.value]) {
            user.homeworkScores[elements.subjectSelect.value] = {};
        }
        
        const currentBest = user.homeworkScores[elements.subjectSelect.value][currentWeek] || 0;
        if (score > currentBest) {
            user.homeworkScores[elements.subjectSelect.value][currentWeek] = score;
        }

        localStorage.setItem('homeworkSubmissions', JSON.stringify(user.homeworkSubmissions));
        localStorage.setItem('homeworkScores', JSON.stringify(user.homeworkScores));

        // Show results
        showPreviousSubmission(user.homeworkSubmissions[submissionKey]);
    } catch (error) {
        console.error('Grammar Review Error:', error);
        if (elements.submissionStatus) {
            elements.submissionStatus.innerHTML = `
                <div style="color: red; text-align: center;">
                    ❌ Error: ${error.message}<br>
                    Please try again later.
                </div>
            `;
        }
    }
}

// Extract text from file
async function extractTextFromFile(file) {
    // For text files
    if (file.type === "text/plain") {
        return await file.text();
    }

    // For PDF files - simplified simulation
    if (file.type === "application/pdf") {
        return "Simulated PDF content: This is a sample homework submission containing various answers and explanations for the assigned exercises.";
    }

    // For image files - simplified simulation
    if (file.type.startsWith("image/")) {
        return "Simulated image content: Handwritten homework answers extracted through OCR simulation.";
    }

    return "File content could not be extracted.";
}

// Analyze text with Dictionary API for vocabulary and grammar
async function analyzeWithDictionaryAPI(text) {
    try {
        const words = text.split(/\s+/).filter(word => word.length > 3);
        const uniqueWords = [...new Set(words)].slice(0, 10); // Analyze first 10 unique words
        
        let vocabularyAnalysis = [];
        let advancedWordCount = 0;
        
        // Analyze each word using dictionary API
        for (const word of uniqueWords) {
            try {
                const cleanWord = word.replace(/[^a-zA-Z]/g, '').toLowerCase();
                if (cleanWord.length < 4) continue;
                
                const response = await fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${cleanWord}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data && data[0]) {
                        const wordData = data[0];
                        const isAdvanced = await checkWordComplexity(cleanWord);
                        if (isAdvanced) advancedWordCount++;
                        
                        vocabularyAnalysis.push({
                            word: cleanWord,
                            meanings: wordData.meanings?.slice(0, 2) || [],
                            advanced: isAdvanced
                        });
                    }
                }
            } catch (wordError) {
                console.log(`Could not analyze word: ${word}`);
            }
            
            // Add delay to avoid rate limiting
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        return generateVocabularyFeedback(vocabularyAnalysis, advancedWordCount, text);
        
    } catch (error) {
        console.error('Dictionary API Error:', error);
        return generateFallbackGrammarFeedback(text);
    }
}

// Check if a word is complex/advanced
async function checkWordComplexity(word) {
    // Simple heuristic for word complexity
    const complexPatterns = [
        /tion$/, /sion$/, /ment$/, /ance$/, /ence$/, /ity$/, /ology$/,
        /^multi/, /^inter/, /^trans/, /^super/, /^hyper/
    ];
    
    return complexPatterns.some(pattern => pattern.test(word)) || word.length > 8;
}

// Generate vocabulary feedback based on analysis
function generateVocabularyFeedback(analysis, advancedCount, originalText) {
    const totalWords = originalText.split(/\s+/).filter(word => word.length > 0).length;
    const advancedPercentage = (advancedCount / Math.max(analysis.length, 1)) * 100;
    const complexityLevel = advancedPercentage >= 30 ? 'Advanced' : advancedPercentage >= 15 ? 'Intermediate' : 'Basic';
    
    let feedback = `<div class="feedback-report">
        <h3>Vocabulary Analysis Report</h3>
        
        <div class="stats-section">
            <h4>Document Statistics:</h4>
            <ul>
                <li><strong>Total words:</strong> ${totalWords}</li>
                <li><strong>Advanced vocabulary:</strong> ${advancedCount} words</li>
                <li><strong>Vocabulary complexity:</strong> ${complexityLevel}</li>
            </ul>
        </div>`;
    
    if (analysis.length > 0) {
        feedback += `
        <div class="word-analysis">
            <h4>Word Analysis:</h4>
            <div class="word-list">`;
        
        analysis.forEach(item => {
            const firstMeaning = item.meanings[0]?.definitions[0]?.definition || 'Definition not available';
            const shortDefinition = firstMeaning.substring(0, 80) + (firstMeaning.length > 80 ? '...' : '');
            feedback += `
                <div class="word-item ${item.advanced ? 'advanced-word' : ''}">
                    <strong>${item.word}</strong>: ${shortDefinition}
                    ${item.advanced ? ' 🎯' : ''}
                </div>`;
        });
        
        feedback += `</div></div>`;
    }
    
    feedback += `
        <div class="suggestions-section">
            <h4>Suggestions for Improvement:</h4>
            <ul>`;
    
    if (advancedPercentage < 15) {
        feedback += `
                <li>Try incorporating more academic vocabulary</li>
                <li>Use synonyms for common words to enhance variety</li>
                <li>Practice using subject-specific terminology</li>`;
    } else if (advancedPercentage >= 30) {
        feedback += `
                <li>Excellent vocabulary range! Maintain this level</li>
                <li>Consider using more nuanced expressions</li>`;
    } else {
        feedback += `
                <li>Good vocabulary foundation - continue expanding</li>
                <li>Try using more precise adjectives and adverbs</li>`;
    }
    
    feedback += `
                <li>Ensure all technical terms are used correctly</li>
                <li>Read academic texts to encounter new vocabulary</li>
            </ul>
        </div>
    </div>`;
    
    return feedback;
}

// Get general feedback for non-English subjects
async function getGeneralFeedback(text, subject, week) {
    // Simple grammar and style analysis
    const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
    const words = text.split(/\s+/).filter(word => word.length > 0);
    const avgSentenceLength = words.length / Math.max(sentences.length, 1);
    
    let feedback = `<div class="feedback-report">
        <h3>Writing Quality Report for ${subject}</h3>
        
        <div class="stats-section">
            <h4>Document Statistics:</h4>
            <ul>
                <li><strong>Total words:</strong> ${words.length}</li>
                <li><strong>Sentences:</strong> ${sentences.length}</li>
                <li><strong>Average sentence length:</strong> ${avgSentenceLength.toFixed(1)} words</li>
            </ul>
        </div>
        
        <div class="assessment-section">
            <h4>Writing Assessment:</h4>
            <ul>`;
    
    // Sentence length analysis
    if (avgSentenceLength > 25) {
        feedback += `<li>Some sentences are quite long. Consider breaking them up for better readability.</li>`;
    } else if (avgSentenceLength < 8) {
        feedback += `<li>Sentences are quite short. Try combining some ideas for better flow.</li>`;
    } else {
        feedback += `<li>Good sentence length variety and readability.</li>`;
    }
    
    feedback += `</ul></div>`;
    
    // Common errors check
    const commonErrors = checkCommonErrors(text);
    if (commonErrors.length > 0) {
        feedback += `
        <div class="improvement-section">
            <h4>Areas for Improvement:</h4>
            <ul>`;
        commonErrors.forEach(error => feedback += `<li>${error}</li>`);
        feedback += `</ul></div>`;
    } else {
        feedback += `
        <div class="positive-feedback">
            <h4>✅ Good job!</h4>
            <p>No major grammatical issues detected. Your writing is clear and well-structured.</p>
        </div>`;
    }
    
    // Add specific subject advice
    feedback += `
        <div class="subject-advice">
            <h4>Subject-Specific Tips:</h4>
            <ul>`;
    
    if (subject.toLowerCase() === 'mathematics') {
        feedback += `
            <li>Use precise mathematical terminology</li>
            <li>Clearly label equations and steps</li>
            <li>Explain your reasoning process</li>`;
    } else if (subject.toLowerCase() === 'physics') {
        feedback += `
            <li>Include units with all numerical values</li>
            <li>Clearly state physical principles used</li>
            <li>Use proper scientific notation</li>`;
    } else if (subject.toLowerCase() === 'biology') {
        feedback += `
            <li>Use correct biological terminology</li>
            <li>Label diagrams clearly</li>
            <li>Explain processes step by step</li>`;
    } else if (subject.toLowerCase() === 'economics') {
        feedback += `
            <li>Define economic terms clearly</li>
            <li>Use graphs and examples effectively</li>
            <li>Connect concepts to real-world applications</li>`;
    } else {
        feedback += `
            <li>Use subject-specific terminology accurately</li>
            <li>Structure your answers logically</li>
            <li>Provide clear explanations for your reasoning</li>`;
    }
    
    feedback += `</ul></div></div>`;
    
    return feedback;
}


// Check for common writing errors
function checkCommonErrors(text) {
    const errors = [];
    
    // Check for passive voice (simplified)
    if (/(was|were) \w+ed/.test(text.toLowerCase())) {
        errors.push("Consider using active voice instead of passive voice for stronger writing");
    }
    
    // Check for repeated words
    const words = text.toLowerCase().split(/\s+/);
    const wordCount = {};
    words.forEach(word => {
        if (word.length > 4) {
            wordCount[word] = (wordCount[word] || 0) + 1;
        }
    });
    
    const repeatedWords = Object.entries(wordCount)
        .filter(([word, count]) => count > 3 && word.length > 4)
        .slice(0, 3);
    
    if (repeatedWords.length > 0) {
        errors.push(`Avoid repeating words: ${repeatedWords.map(([word]) => word).join(', ')}`);
    }
    
    // Check sentence structure variety
    const sentences = text.split(/[.!?]+/);
    const startingWords = sentences.map(s => s.trim().split(' ')[0].toLowerCase()).slice(0, 5);
    const uniqueStarters = new Set(startingWords).size;
    
    if (uniqueStarters < 3 && sentences.length >= 5) {
        errors.push("Vary your sentence beginnings for better flow");
    }
    
    return errors;
}

// Calculate grammar score based on analysis
function calculateGrammarScore(feedback, originalText) {
    let score = 16; // Base score
    
    const words = originalText.split(/\s+/);
    const sentences = originalText.split(/[.!?]+/).filter(s => s.trim().length > 0);
    
    // Score based on text length and structure
    if (words.length > 100) score += 1;
    if (words.length > 200) score += 1;
    
    const avgSentenceLength = words.length / Math.max(sentences.length, 1);
    if (avgSentenceLength >= 12 && avgSentenceLength <= 25) score += 1;
    
    // Score adjustments based on feedback content
    if (feedback.includes('Excellent') || feedback.includes('Outstanding')) score += 2;
    if (feedback.includes('Advanced vocabulary')) score += 1;
    if (feedback.includes('basic') || feedback.includes('simple vocabulary')) score -= 1;
    if (feedback.includes('repeating words') || feedback.includes('passive voice')) score -= 1;
    
    // Ensure score is between 8 and 20
    return Math.max(8, Math.min(20, score));
}

// Generate fallback feedback when API is unavailable
function generateFallbackGrammarFeedback(text) {
    const words = text.split(/\s+/).filter(word => word.length > 0);
    const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
    
    return `<div class="feedback-report">
        <h3>Writing Analysis (Basic)</h3>
        
        <div class="stats-section">
            <h4>Document Overview:</h4>
            <ul>
                <li><strong>Word count:</strong> ${words.length}</li>
                <li><strong>Sentence count:</strong> ${sentences.length}</li>
                <li><strong>Average sentence length:</strong> ${(words.length / Math.max(sentences.length, 1)).toFixed(1)} words</li>
            </ul>
        </div>
        
        <div class="general-feedback">
            <h4>General Feedback:</h4>
            <p>Your submission shows good effort. For more detailed grammar and vocabulary analysis, please ensure you have a stable internet connection.</p>
            <p>Consider varying your sentence structure and using more precise vocabulary to enhance your writing quality.</p>
        </div>
    </div>`;
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM Content Loaded - Initializing Homework Page");
    
    // Initialize data first
    initializeUserData();
    
    // Refresh user data
    user = getUserData();
    
    // Get DOM elements
    elements = getDOMElements();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial weeks
    loadWeeks();
    
    console.log("Homework page initialized successfully!");
});

// Add error handler for uncaught errors
window.addEventListener('error', function(e) {
    console.error('Global error caught:', e.error);
    console.error('In file:', e.filename, 'at line:', e.lineno);
});