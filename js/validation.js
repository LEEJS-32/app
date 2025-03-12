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
