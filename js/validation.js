function validateForm() {
    console.log("Validation started");

    let nameField = document.getElementById("name");
    let emailField = document.getElementById("email");
    let passwordField = document.getElementById("password");

    let name = nameField ? nameField.value.trim() : "";  // Check if `name` exists
    let email = emailField ? emailField.value.trim() : "";
    let password = passwordField ? passwordField.value : "";

    let isValid = true;
    let passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/;

    // Name Validation (Only if `name` field exists)
    if (nameField && name === "") {
        document.getElementById("name_error1").innerText = "Name is required";  
        isValid = false;
    } else if (nameField) {
        document.getElementById("name_error1").innerText = "";
    }

    // Email Validation
    if (email === "") {
        document.getElementById("email_error1").innerText = "Email is required";    
        isValid = false;
    } else {
        document.getElementById("email_error1").innerText = "";
    }

    // Password Validation
    if (password === "") {
        document.getElementById("pwd_error1").innerText = "Password is required";
        isValid = false;
    } else if (password.length < 8) {
        document.getElementById("pwd_error1").innerText = "Password must be at least 8 characters";
        isValid = false;
    } else {
        document.getElementById("pwd_error1").innerText = "";
    }

    if (password !== "" && !passwordRegex.test(password)) {
        document.getElementById("pwd_error2").innerText = "Password must contain 1 uppercase letter, 1 number, and 1 special character (@, #, $, etc.)";
        isValid = false;
    } else {
        document.getElementById("pwd_error2").innerText = "";
    }

    return isValid;
}

function validateLoginForm() {
    let isValid = true;
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const emailError = document.getElementById('email_error1');
    const passwordError = document.getElementById('pwd_error1');

    // Reset error messages
    emailError.textContent = '';
    passwordError.textContent = '';

    // Email validation
    if (!email) {
        emailError.textContent = 'Email is required';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailError.textContent = 'Please enter a valid email address';
        isValid = false;
    }

    // Password validation
    if (!password) {
        passwordError.textContent = 'Password is required';
        isValid = false;
    } else if (password.length < 8) {
        passwordError.textContent = 'Password must be at least 8 characters long';
        isValid = false;
    } else if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(password)) {
        passwordError.textContent = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)';
        isValid = false;
    }

    return isValid;
}

function validateSignupForm() {
    let isValid = true;
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const nameError = document.getElementById('name_error1');
    const emailError = document.getElementById('email_error1');
    const passwordError = document.getElementById('pwd_error1');

    // Reset error messages
    nameError.textContent = '';
    emailError.textContent = '';
    passwordError.textContent = '';

    // Name validation
    if (!name) {
        nameError.textContent = 'Name is required';
        isValid = false;
    } else if (name.length < 2) {
        nameError.textContent = 'Name must be at least 2 characters long';
        isValid = false;
    }

    // Email validation
    if (!email) {
        emailError.textContent = 'Email is required';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailError.textContent = 'Please enter a valid email address';
        isValid = false;
    }

    // Password validation
    if (!password) {
        passwordError.textContent = 'Password is required';
        isValid = false;
    } else if (password.length < 8) {
        passwordError.textContent = 'Password must be at least 8 characters long';
        isValid = false;
    } else if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(password)) {
        passwordError.textContent = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)';
        isValid = false;
    }

    return isValid;
}
