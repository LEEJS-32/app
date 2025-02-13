<?php

$servername = "localhost";
$username = "root";
$password = "";


$conn = new mysqli($servername, $username, $password);

$sql = "DROP database testing1";
$result = $conn->query($sql);
if ($result == true) {
    echo "Database deleted successfully";
}

?>