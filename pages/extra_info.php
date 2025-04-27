<?php
require_once '../_base.php';
auth_user();


$user = $_SESSION['user'];
$name = $user['name'];
$email = $user['email'];

$new_user = true;
$_SESSION["new_user"] = $new_user;

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <!-- <title>Header</title> -->
        <link rel="stylesheet" href="\css\header.css">
        <link rel="stylesheet" href="\css\logo.css">
        <link rel="stylesheet" href="\css\style.css">
        <link rel="stylesheet" href="\css\extra_info.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    </head>
    <script>
        function switchSection(index) {
            document.querySelector(".container").style.transform = `translateX(${index * -33.33}%)`;
        }

        function toggleSelection(option) {
            option.classList.toggle("selected");
        }

        function toggleSelection(option) {
        option.classList.toggle("selected");

        let selectedStyles = [];
        document.querySelectorAll(".furniture-option.selected").forEach(option => {
            selectedStyles.push(option.textContent);
        });

        document.getElementById("preference").value = selectedStyles.join(",");
    }
    </script>
<body>

<form method="post" action="../backend/extra_info_process.php">
    <div class="wrapper">
        <h1>Let us Know More About You...</h1>
        <div class="container">
            <!-- Section 1: Basic Info -->
            <div class="section">
                <div class="subtitle">
                <h2>Step 1: Tell Us About Yourself</h2>
                </div>
                <div class="form">
                <label for="gender">Gender</label>
                <br><input type="radio" name="gender" value="Male">Male
                <input type="radio" name="gender" value="Female">Female
                <br><br>
                <label for="phonenum">Phone Number</label>
                <br><input type="tel" id="phonenum" name="phonenum">
                <br><br>
                <label for="dob">Date of Birth</label>
                <br><input type="date" id="dob" name="dob">
                <br><br>
                <label for="occupation">Occupation</label>
                <br><input type="text" id="occupation" name="occupation">
                <br><br>

                <button type="button" onclick="switchSection(1)">Next</button>
   
            </div>
            </div>

            <!-- Section 2: Address Details -->
            <div class="section">
                <div class="subtitle">
                <h2>Step 2: Where Do You Live?</h2>
                </div>
                <div class="form">
                    <label for="address-line1">Address Line 1</label>
                    <br><input type="text" id="address-line1" name="address-line1">
                    <br><br>
                    <label for="address-line2">Address Line 2</label>
                    <br><input type="text" id="address-line2" name="address-line2">
                    <br><br>
                    <div class="divider">
                    <div class="div1">
                    <label for="city">City</label>
                    <br><input type="text" id="city" name="city">
                    </div>
                    <div class="div2">
                    <label for="country">Country</label>
                    <br><input type="text" id="country" name="country">
                    </div>
                    </div>
                    <br>
                    <label for="postcode">Post Code</label>
                    <br><input type="number" id="postcode" name="postcode">
                    <br><br>

                <button type="button" onclick="switchSection(0)">Back</button>
                <button type="button" onclick="switchSection(2)">Next</button>
 
            </div>
            </div>

            <!-- Section 3: Preferences -->
            <div class="section">
                <div class="subtitle">
                <h2>Step 3: Select Your Favorite Styles</h2>
                </div>
                
                <div class="furniture-options">
                    <div class="furniture-option" onclick="toggleSelection(this)">Modern</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Minimalist</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Classic</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Muji</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Japandi</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Bohemian</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Vintage</div>
                    <div class="furniture-option" onclick="toggleSelection(this)">Asian Decor</div>
                </div>
                <input type="hidden" id="preference" name="preference">
                <br><br>
                <div class="form">
                    <div class="buttons">
                <button type="button" onclick="switchSection(1)">Back</button>
                <button type="submit">Done</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</form>

</main>
</body>
</html>
