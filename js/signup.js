// signup.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    
    // Email validation function
    function validateEmail(email) {
        // Basic email format validation
        const basicEmailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!basicEmailRegex.test(email)) {
            return false;
        }

        // Check for allowed domains
        const allowedDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'admin.fas.com'];
        const domain = email.split('@')[1].toLowerCase();
        return allowedDomains.includes(domain);
    }

    // Real-time email validation
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    
    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        if (email === '') {
            emailError.textContent = 'Email is required';
        } else if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address (gmail.com, yahoo.com, hotmail.com, or admin.fas.com)';
        } else {
            emailError.textContent = '';
        }
    });

    // Password validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        return password.length >= minLength && hasUpperCase && hasLowerCase && 
               hasNumbers && hasSpecialChar;
    }

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        if (!validatePassword(password)) {
            passwordError.textContent = 'Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters';
        } else {
            passwordError.textContent = '';
        }
    });

    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            confirmPasswordError.textContent = 'Passwords do not match';
        } else {
            confirmPasswordError.textContent = '';
        }
    });

    // Username validation
    const usernameInput = document.getElementById('username');
    const usernameError = document.getElementById('usernameError');

    usernameInput.addEventListener('input', function() {
        const username = this.value.trim();
        if (username.length < 3) {
            usernameError.textContent = 'Username must be at least 3 characters long';
        } else if (username.length > 20) {
            usernameError.textContent = 'Username must not exceed 20 characters';
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            usernameError.textContent = 'Username can only contain letters, numbers, and underscores';
        } else {
            usernameError.textContent = '';
        }
    });

    // Age validation
    const ageInput = document.getElementById('age');
    const ageError = document.getElementById('ageError');

    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (isNaN(age) || age < 13 || age > 120) {
            ageError.textContent = 'Please enter a valid age between 13 and 120';
        } else {
            ageError.textContent = '';
        }
    });

    // Height and weight validation
    const heightInput = document.getElementById('height');
    const weightInput = document.getElementById('weight');
    const heightError = document.getElementById('heightError');
    const weightError = document.getElementById('weightError');

    heightInput.addEventListener('input', function() {
        const height = parseFloat(this.value);
        if (isNaN(height) || height < 50 || height > 300) {
            heightError.textContent = 'Please enter a valid height between 50cm and 300cm';
        } else {
            heightError.textContent = '';
        }
    });

    weightInput.addEventListener('input', function() {
        const weight = parseFloat(this.value);
        if (isNaN(weight) || weight < 20 || weight > 500) {
            weightError.textContent = 'Please enter a valid weight between 20kg and 500kg';
        } else {
            weightError.textContent = '';
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        // Prevent form submission if there are any validation errors
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const username = usernameInput.value.trim();
        const age = parseInt(ageInput.value);
        const height = parseFloat(heightInput.value);
        const weight = parseFloat(weightInput.value);
        const gender = document.getElementById('gender').value;
        const fitnessGoal = document.getElementById('fitnessGoal').value;

        let hasError = false;

        // Check all validations
        if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            hasError = true;
        }

        if (!validatePassword(password)) {
            hasError = true;
        }

        if (password !== confirmPassword) {
            hasError = true;
        }

        if (username.length < 3 || username.length > 20 || !/^[a-zA-Z0-9_]+$/.test(username)) {
            hasError = true;
        }

        if (isNaN(age) || age < 13 || age > 120) {
            hasError = true;
        }

        if (isNaN(height) || height < 50 || height > 300) {
            hasError = true;
        }

        if (isNaN(weight) || weight < 20 || weight > 500) {
            hasError = true;
        }

        if (!gender) {
            document.getElementById('genderError').textContent = 'Please select a gender';
            hasError = true;
        }

        if (!fitnessGoal) {
            document.getElementById('fitnessGoalError').textContent = 'Please select a fitness goal';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
        }
    });
});