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
    <link rel="stylesheet" href="../../css/member/member_profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>

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
        $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $name = $row['name'];
            
        // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }
    ?>
    

    
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
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
                <li><a href="order_history.php"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>My Profile</h1>


            <div class="profile-wrapper">
        <div class="avatar-section">
        <div class="avatar-container">
            <img id="avatar" src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
            <video id="video" autoplay class="profile-avatar" style="display: none;"></video>
            <button class="avatar-upload-btn" id="toggleOptions">+</button>
        </div>
    </div>
    <div class="upload-guideline">
    <h2 class="head"><?php echo ($row['name']) ?><img src="../../img/tick.jpg" width="38px" height="38px"></h2>
        <div class="general-infos">
            <div class="g-info">
                <p>Role</p>
                <p>Member</p>
            </div>
            <div class="g-info">
                <p>Email Address</p>
                <p><?php echo ($row['email']) ?></p>
            </div>
            <div class="g-info">
                <p>Reward Pts.</p>
                <p><?php echo ($row['reward_pt']) ?></p>
            </div>
        </div>

        <div class="options" id="uploadOptions">
  <form id="fileUploadForm" action="../../backend/file_upload_photo.php" method="POST" enctype="multipart/form-data">
    <label for="fileInput">
      <i class='bx bxs-folder-plus'></i> Upload
    </label>
    <input type="file" id="fileInput" name="profile_photo" accept="image/*" hidden>
  </form>

  <button id="openCamera" type="button">
    <i class='bx bx-camera'></i> Camera
  </button>
</div>



    </div>
    <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>

    <form id="webcamUploadForm" action="../../backend/upload_photo.php" method="POST">
        <input type="hidden" name="image" id="imageData">
        <button type="submit" id="upload" style="display: none;">Upload</button>
    </form>

</div>



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
    <div class="info-card">
    <div class="info-header">
        <h3>Personal Info</h3>
        <button type="button" class="edit-btn" id="toggleEdit">✎ Edit</button>

    </div>

    <div id="readonly-view">
    <div class="info-grid">
        <div class="info-item">
            <label>Full Name</label>
            <p><?= htmlspecialchars($name) ?></p>
        </div>
        <div class="info-item">
            <label>Email</label>
            <p><?php echo ($email) ?></p>
        </div>
        <div class="info-item">
            <label>Phone</label>
            <p><?= htmlspecialchars($GLOBALS['phonenum']) ?></p>
        </div>
        <div class="info-item">
            <label>Gender</label>
            <p><?= htmlspecialchars($GLOBALS['gender']) ?></p>
        </div>
        <div class="info-item">
            <label>Date of Birth</label>
            <p><?= htmlspecialchars($GLOBALS['dob']) ?></p>
        </div>
        <div class="info-item">
            <label>Occupation</label>
            <p><?= htmlspecialchars($GLOBALS['occupation']) ?></p>
        </div>
    </div>
    </div>

    <!-- Hidden editable form -->
    <form method="post" id="editForm" action="../../backend/extra_info_process.php" style="display:none;">
        <div class="info-grid">
            <div class="info-item">
                <label for="name">Name</label>
                <?php html_text('name'); ?>
            </div>
            <div class="info-item">
                <label for="email">Email</label>
                <?php html_text('email'); ?>
            </div>
            <div class="info-item">
                <label for="phonenum">Phone Number</label>
                <input type="tel" id="phonenum" name="phonenum" value="<?= htmlspecialchars($GLOBALS['phonenum']) ?>">
            </div>
            <div class="info-item">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($GLOBALS['dob']) ?>">
            </div>
            <div class="info-item">
                <label for="occupation">Occupation</label>
                <input type="text" id="occupation" name="occupation" value="<?= htmlspecialchars($GLOBALS['occupation']) ?>">
            </div>
            <div class="info-item-gender">
                <label for="gender">Gender</label>
                <div>
                <div>
                <input type="radio" id="gender" name="gender" value="male" <?= $GLOBALS['gender'] == 'male' ? 'checked' : '' ?>> Male
                </div>
                <div>
                <input type="radio" id="gender" name="gender" value="female" <?= $GLOBALS['gender'] == 'female' ? 'checked' : '' ?>> Female
                </div>
                </div>
            </div>
        </div>
        <button type="submit" class="confirm-btn">Save</button>
    </form>
</div>

        </div>  
    </div>

    <!-- adding  -->
    <script>
        document.getElementById("fileInput").addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();

                // Live preview
                reader.onload = function (e) {
                    document.getElementById("avatar").src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Submit form after preview
                document.getElementById("fileUploadForm").submit();
            }
        });
    </script>

    

    <script>
        const toggleBtn = document.getElementById('toggleOptions');
        const options = document.getElementById('uploadOptions');
        const video = document.getElementById('video');
        const avatar = document.getElementById('avatar');
        const canvas = document.getElementById('canvas');
        const imageData = document.getElementById('imageData');
        const webcamUploadForm = document.getElementById('webcamUploadForm'); // ✅ changed form ID
        const fileInput = document.getElementById('fileInput');
        const fileUploadForm = document.getElementById('fileUploadForm'); // ✅ new ID for file form

        let cameraOn = false;

        // 🔘 Toggle camera/capture action
        toggleBtn.addEventListener('click', () => {
            if (cameraOn) {
                // Capture photo from webcam
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/png');
                imageData.value = dataURL;

                // Submit the correct form (webcam)
                webcamUploadForm.submit();
            } else {
                options.classList.toggle('show');
            }
        });

        // 🎥 Open webcam
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

        // 📁 Handle file input upload with preview and auto-submit
        fileInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatar.src = e.target.result;
                };
                reader.readAsDataURL(file);

                fileUploadForm.submit(); // ✅ use correct form
            }
        });
    </script>


<script>
document.getElementById("toggleEdit").addEventListener("click", function () {
    const form = document.getElementById("editForm");
    const readonly = document.getElementById("readonly-view");

    const isVisible = form.style.display === "block";

    if (isVisible) {
        form.style.display = "none";
        readonly.style.display = "block";
        this.innerHTML = "✎ Edit";
    } else {
        form.style.display = "block";
        readonly.style.display = "none";
        this.innerHTML = "✖ Cancel";
    }
});
</script>


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
    