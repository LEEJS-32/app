<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

//member role
auth_user();
auth('member');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];
$_genders = ['male' => 'Male', 'female' => 'Female'];
// ----------------------------------------------------------------------------
?>
</script>
<head>
    <title>Member Profile</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>
    <style>
  .profile-wrapper {
    position: relative;
    width: 100px;
    height: 150px;
    margin: 0 auto;
  }

  .profile-container {
    position: relative;
    width: 150px;
    height: 150px;
  }

  .profile-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
  }

  .upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: black;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    font-size: 20px;
    z-index: 2;
    transition: all 0.3s ease;
  }

  .options {
    position: absolute;
    top: 160px;
    left: 50%;
    transform: translateX(-50%);
    display: none;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    z-index: 1;
  }

  .options.show {
    display: flex;
  }

  .options button {
    padding: 8px 14px;
    background: white;
    border: 1px solid #ccc;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    font-size: 14px;
  }
</style>

</head>

<body>
    <header>
        <?php 
            include __DIR__ . '/../../_header.php'; 
        ?>
    </header>

    <main>
    <?php
        require '../../db/db_connect.php';

        // Fetch avatar from database
        $sql = "SELECT avatar FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
        // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }
    ?>
    

    
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo ($name); ?></h3>
                    <p><?php echo ($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="member_profile.php"  class="active"><i class='bx bxs-user-detail' ></i>My Profile</a></li>
                <li><a href="reset_password.php"><i class='bx bx-lock-alt' ></i>Password </a></li>
                <li><a href="member_address.php"><i class='bx bx-home-alt-2' ></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag' ></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart' ></i>Wishlist</a></li>
                <li><a href="#"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Member Profile</h1>


            <div class="profile-wrapper">
            <div class="profile-container">
                <img id="avatar" src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Profile" class="profile-image" />
                <video id="video" autoplay class="profile-image" style="display: none;"></video>
                <button class="upload-btn" id="toggleOptions">+</button>
            </div>

            <div class="options" id="uploadOptions">
                <button id="openCamera" type="button"><i class='bx bx-camera' ></i></button>
                <button onclick="document.getElementById('fileInput').click()" type="button"><i class='bx bxs-folder-plus'></i></button>
                <input type="file" id="fileInput" style="display: none;" onchange="document.getElementById('uploadForm').submit();">
            </div>
            </div>

            <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>

            <form id="uploadForm" action="../../backend/upload_photo.php" method="POST">
            <input type="hidden" name="image" id="imageData">
            <button type="submit" id="upload" style="display: none;">Upload</button>
            </form>

            <!-- Edit Profile -->
<?php 

require __DIR__ . '/../../db/db_connect.php';
$sql = "SELECT password, gender, phonenum, dob, occupation FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Store retrieved values in variables
    $GLOBALS['gender'] = $row['gender'];
    $GLOBALS['phonenum'] = $row['phonenum'];
    $GLOBALS['dob'] = $row['dob'];
    $GLOBALS['occupation'] = $row['occupation'];
}

?> 
    <h1>Update Your Profile</h1>
    <form method="post" id="#editForm" action="../../backend/extra_info_process.php">
    <label for="gender">Gender:</label>
                <?php html_radios('gender', $_genders, true, 'disabled')?>
                <br>
                <label for="phonenum">Phone Number:</label>
                <input type="tel" id="phonenum" name="phonenum" value="<?php echo encode($GLOBALS['phonenum']); ?>" disabled>
                <br><br>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?php echo encode($GLOBALS['dob']); ?>" disabled>
                <br><br>
                <label for="occupation">Occupation:</label>
                <?php html_text('occupation', 'disabled')?>
                <br><br>
                <button type="button" id="edit-btn" onclick="enableForm()">Edit</button>
                <button type="submit" id="confirm-btn" style="display: none;">Confirm</button>
    </form>
        </div>  
    </div>


    <script>
        const toggleBtn = document.getElementById('toggleOptions');
        const options = document.getElementById('uploadOptions');
        const video = document.getElementById('video');
        const avatar = document.getElementById('avatar');
        const canvas = document.getElementById('canvas');
        const imageData = document.getElementById('imageData');
        const uploadForm = document.getElementById('uploadForm');

        let cameraOn = false;

        toggleBtn.addEventListener('click', () => {
            if (cameraOn) {
            // Capture the photo
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataURL = canvas.toDataURL('image/png');
            imageData.value = dataURL;
            uploadForm.submit();
            } else {
            options.classList.toggle('show');
            }
        });

        document.getElementById('openCamera').addEventListener('click', async () => {
            try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            video.style.display = 'block';
            avatar.style.display = 'none';
            options.classList.remove('show');
            toggleBtn.textContent = '📸';
            cameraOn = true;
            } catch (err) {
            alert('Failed to access camera: ' + err.message);
            }
        });
    </script>

    <script>
        function enableForm() {
    // Enable all input fields
    document.querySelectorAll("form input, form select").forEach(input => {
        input.removeAttribute("disabled");
    });

    // Hide "Edit" button and show "Confirm" button
    document.getElementById("edit-btn").style.display = "none";
    document.getElementById("confirm-btn").style.display = "inline-block";
    }
    </script>

    <!-- Load reCAPTCHA script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    </main>

    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
    