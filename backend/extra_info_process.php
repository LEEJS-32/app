<?php
include '../_base.php';

auth_user();
auth();

var_dump($_SESSION['user']);

$user = $_SESSION['user'];
$user_id = $user->user_id;

echo ($user_id);

$new_user = $_SESSION['new_user'];

$name = post('name') ?? null;
$email = post('email') ?? null;
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

try {
    $stm = $_db->prepare("UPDATE users SET name=:name, email=:email, gender=:gender, phonenum=:phonenum, preference=:preference, dob=:dob, occupation=:occupation WHERE user_id=:user_id");
    $stm->execute([
        ':name' => $name,
        ':email' => $email,
        ':gender' => $gender,
        ':phonenum' => $phonenum,
        ':preference' => $preference,
        ':dob' => $dob,
        ':occupation' => $occupation,
        ':user_id' => $user_id
    ]);

    echo "Data submitted successfully!";

    if (($_user) && ($_user->role == "member")) {
        redirect("../pages/member/member_profile.php");
    }
    else if (($_user) && ($_user->role == "admin")) {
        if ((!$new_user) && ($_user) && ($_user->role == "member")) {
            redirect("../pages/member/member_profile.php");
        }
        else if ((!$new_user) && ($_user) && ($_user->role == "admin")) {
            redirect("../pages/member/admin_profile.php");
        }
        else if ($new_user){
            redirect("../pages/signup_login.php"); // Redirect to a success page
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>