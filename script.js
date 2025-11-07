
// Switching between login and register forms 
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const showLogin = document.getElementById('show-login');
const showRegister = document.getElementById('show-register');

// Switch to Register form
showRegister.addEventListener('click', (e) => {
  e.preventDefault();
  loginForm.classList.add('hidden');
  registerForm.classList.remove('hidden');
  clearAllErrors();
});

// Switch to Login form
showLogin.addEventListener('click', (e) => {
  e.preventDefault();
  registerForm.classList.add('hidden');
  loginForm.classList.remove('hidden');
  clearAllErrors();
});

// Clear all error messages and styles
function clearAllErrors() {
  const errorMessages = document.querySelectorAll('.error-message');
  errorMessages.forEach(error => {
    error.textContent = '';
    error.classList.add('hidden');
  });
  
  const inputs = document.querySelectorAll('input, select');
  inputs.forEach(input => {
    input.classList.remove('input-error');
  });
  
  document.getElementById('password-strength').classList.add('hidden');
}

// Show error for a specific field
function showError(fieldId, message) {
  const field = document.getElementById(fieldId);
  const errorElement = document.getElementById(`${fieldId}-error`);
  
  field.classList.add('input-error');
  errorElement.textContent = message;
  errorElement.classList.remove('hidden');
}

// Clear error for a specific field
function clearError(fieldId) {
  const field = document.getElementById(fieldId);
  const errorElement = document.getElementById(`${fieldId}-error`);
  
  field.classList.remove('input-error');
  errorElement.textContent = '';
  errorElement.classList.add('hidden');
}

// Check password strength and provide feedback
function checkPasswordStrength(password) {
  const strengthElement = document.getElementById('password-strength');
  
  if (password.length === 0) {
    strengthElement.classList.add('hidden');
    return;
  }
  
  let strength = 0;
  let feedback = '';
  
  // Check length
  if (password.length >= 8) strength++;
  
  // Check for lowercase
  if (/[a-z]/.test(password)) strength++;
  
  // Check for uppercase
  if (/[A-Z]/.test(password)) strength++;
  
  // Check for numbers
  if (/\d/.test(password)) strength++;
  
  // Check for special characters
  if (/[@$!%*?&]/.test(password)) strength++;
  
  // Determine strength level
  if (strength <= 2) {
    feedback = 'Weak password';
    strengthElement.className = 'password-strength strength-weak';
  } else if (strength <= 4) {
    feedback = 'Medium password';
    strengthElement.className = 'password-strength strength-medium';
  } else {
    feedback = 'Strong password';
    strengthElement.className = 'password-strength strength-strong';
  }
  
  strengthElement.textContent = feedback;
  strengthElement.classList.remove('hidden');
}

// Real-time validation for password field
document.getElementById('password').addEventListener('input', function() {
  checkPasswordStrength(this.value);
  clearError('password');
});

// REGISTER FORM VALIDATION
document.getElementById('register-form').addEventListener('submit', function(event) {
  event.preventDefault(); // stop auto form submission
  
  // Clear previous errors
  clearAllErrors();
  
  // Collect form inputs
  let fname = document.getElementById('fname');
  let lname = document.getElementById('lname');
  let dob = document.getElementById('dob');
  let gender = document.getElementById('gender');
  let email = document.getElementById('email');
  let phone = document.getElementById('phone').value.trim();
  let countryCode = document.getElementById('countryCode').value;
  let password = document.getElementById('password').value;
  let confirmPassword = document.getElementById('confirmPassword').value;
  let role = document.getElementById('role').value;
  let isValid = true;

  // Validate First Name
  if (fname.value.trim() === '') {
    showError('fname', 'First name is required');
    isValid = false;
  } else {
    fname.value = fname.value.trim().replace(/\b\w/g, char => char.toUpperCase());
  }

  // Validate Last Name
  if (lname.value.trim() === '') {
    showError('lname', 'Last name is required');
    isValid = false;
  } else {
    lname.value = lname.value.trim().replace(/\b\w/g, char => char.toUpperCase());
  }

  // Validate Date of Birth
  if (dob.value === '') {
    showError('dob', 'Date of birth is required');
    isValid = false;
  } else {
    // Check if user is at least 13 years old
    const dobDate = new Date(dob.value);
    const today = new Date();
    const age = today.getFullYear() - dobDate.getFullYear();
    const monthDiff = today.getMonth() - dobDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dobDate.getDate())) {
      age--;
    }
    
    if (age < 13) {
      showError('dob', 'You must be at least 13 years old');
      isValid = false;
    }
  }

  // Validate Gender
  if (gender.value === '') {
    showError('gender', 'Please select your gender');
    isValid = false;
  }

  // Validate Email
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (email.value.trim() === '') {
    showError('email', 'Email is required');
    isValid = false;
  } else if (!emailPattern.test(email.value.trim())) {
    showError('email', 'Please enter a valid email address');
    isValid = false;
  }

  // Validate Phone
  if (phone === '') {
    showError('phone', 'Phone number is required');
    isValid = false;
  } else if (!/^\d+$/.test(phone)) {
    showError('phone', 'Phone number must contain digits only');
    isValid = false;
  }

  // Validate Password
  const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (password === '') {
    showError('password', 'Password is required');
    isValid = false;
  } else if (!strongPasswordPattern.test(password)) {
    showError('password', 'Password must be at least 8 characters long, with uppercase, lowercase, number, and symbol');
    isValid = false;
  }

  // Validate Confirm Password
  if (confirmPassword === '') {
    showError('confirmPassword', 'Please confirm your password');
    isValid = false;
  } else if (password !== confirmPassword) {
    showError('confirmPassword', 'Passwords do not match');
    isValid = false;
  }

  // Validate Role
  if (role === '') {
    showError('role', 'Please select a role');
    isValid = false;
  }

  // If all validations pass
  if (isValid) {
    let fullPhone = `${countryCode}${phone}`;
    alert(`Registration successful for ${fname.value} (${role})!\nPhone: ${fullPhone}`);

    // Redirect to dashboard
    window.location.href = "dashboard.html";
  }
});

// =========================
// LOGIN FORM VALIDATION
// =========================
document.getElementById('login-form').addEventListener('submit', function(event) {
  event.preventDefault();
  
  // Clear previous errors
  clearAllErrors();
  
  let loginId = document.getElementById('login-identifier').value.trim();
  let loginPassword = document.getElementById('login-password').value.trim();
  let isValid = true;

  // Validate login identifier
  if (loginId === '') {
    showError('login-identifier', 'Email or username is required');
    isValid = false;
  }

  // Validate password
  if (loginPassword === '') {
    showError('login-password', 'Password is required');
    isValid = false;
  }

  // If all validations pass
  if (isValid) {
    alert("Login successful!");
    window.location.href = "dashboard.html";
  }
});
