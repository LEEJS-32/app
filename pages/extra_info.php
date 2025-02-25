<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extra Info</title>
    <link rel="stylesheet" href="../css/extra_info.css">
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
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: /pages/signup_login.php");
    exit();
}
$email = $_SESSION['email'];
$name = $_SESSION['name'];
?>
<form method="post" action="../backend/extra_info_process.php">
    <div class="wrapper">
        <div class="container">
            <!-- Section 1: Basic Info -->
            <div class="section">
                <h2>Step 1: Tell Us About Yourself</h2>
                <label for="gender">Gender:</label>
                <input type="radio" name="gender" value="Male">Male
                <input type="radio" name="gender" value="Female">Female
                <br><br>
                <label for="phonenum">Phone Number:</label>
                <input type="tel" id="phonenum" name="phonenum">
                <br><br>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob">
                <br><br>
                <label for="occupation">Occupation:</label>
                <input type="text" id="occupation" name="occupation">
                <br><br>
                <button type="button" onclick="switchSection(1)">Next</button>
            </div>

            <!-- Section 2: Address Details -->
            <div class="section">
                <h2>Step 2: Where Do You Live?</h2>
                <fieldset>
                    <legend>Address</legend>
                    <label for="address-line1">Address Line 1:</label>
                    <input type="text" id="address-line1" name="address-line1">
                    <br><br>
                    <label for="address-line2">Address Line 2:</label>
                    <input type="text" id="address-line2" name="address-line2">
                    <br><br>
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city">
                    <br><br>
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country">
                    <br><br>
                    <label for="postcode">Post Code:</label>
                    <input type="number" id="postcode" name="postcode">
                    <br><br>
                </fieldset>
                <button type="button" onclick="switchSection(0)">Back</button>
                <button type="button" onclick="switchSection(2)">Next</button>
            </div>

            <!-- Section 3: Preferences -->
            <div class="section">
                <h2>Step 3: Select Your Favorite Furniture Styles</h2>
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
                <button type="button" onclick="switchSection(1)">Back</button>
                <button type="submit">Done</button>
            </div>
        </div>
    </div>
</form>


</body>
</html>
