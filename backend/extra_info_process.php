<?php
include '../_base.php';

auth_user();
auth();

$user = $_SESSION['user'];
$user_id = $user['user_id'];

$new_user = $_SESSION['new_user'];

$servername = "localhost";
$username = "root";
$password = "";
$database = "TESTING1";

$conn = new mysqli($servername, $username, $password, $database);

$gender = post('gender') ?? null;
$phonenum = post('phonenum') ?? null;
$dob = post('dob') ?? null;
$occupation = post('occupation') ?? null;
$address1 = post('address-line1') ?? null;
$address2 = post('address-line2') ?? null;
$city = post('city') ?? null;
$country = post('country') ?? null;
$postcode = post('postcode') ?? null;
$preference = post('preference') ?? null;

$stmt = $conn->prepare("UPDATE users SET gender=?, phonenum=?, preference=?, dob=?, occupation=? WHERE user_id=?");
$stmt->bind_param("sssssi",  $gender, $phonenum, $preference, $dob, $occupation, $user_id);

// Execute SQL query
if ($stmt->execute()) {
    echo "Data submitted successfully!";
    if ((!$new_user) && ($_user) && ($_user['role'] == "member")) {
        redirect("../pages/member/member_profile.php");
    }
    else if ((!$new_user) && ($_user) && ($_user['role'] == "admin")) {
        redirect("../pages/member/admin_profile.php");
    }
    else if ($new_user){
        redirect("../pages/signup_login.php"); // Redirect to a success page
    }
}
else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>