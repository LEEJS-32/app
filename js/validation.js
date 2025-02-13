function validateForm() {
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value;

    let isValid = true;
    let passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/;

    if (name == "") {
        document.getElementById("name_error1").innerText = "Name is required";    
        isValid = false;
    }
    else {
        document.getElementById("name_error1").innerText = "";
    }

    if (email == "") {
        document.getElementById("email_error1").innerText = "Email is required";    
        isValid = false;
    }
    else {
        document.getElementById("email_error1").innerText = "";     
    }

    if (password == "") {
        document.getElementById("pwd_error1").innerText = "Password is required";
        isValid = false;
    }
    else if (password.length < 8){
        document.getElementById("pwd_error1").innerText = "Password must be equal or longer than 8 character";
        isValid = false;
    }
    else {
        document.getElementById("pwd_error1").innerText = "";
    }
    if (password != "" && !passwordRegex.test(password)){
        document.getElementById("pwd_error2").innerText = "Password must contain at least 1 uppercase letter, 1 number, and 1 special character (@, #, $, etc.)";
        isValid = false;
    }
    else {
        document.getElementById("pwd_error2").innerText = "";
    }
        
    return isValid;

}