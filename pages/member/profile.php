<?php
session_start();
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <!-- <title>Header</title> -->
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="../../css/style.css">
        <script></script>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    </head>

    <body>
        <header>
        <?php include '../../_header.php'?>
        </header>

        <main>
        <h2>Webcam Capture</h2>
    <video id="video" width="320" height="240" autoplay></video>
    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
    <br>
    <button onclick="takeSnapshot()">Capture</button>
    <button onclick="uploadImage()">Upload</button>
    <br>
    <img id="snapshot" src="" width="320" height="240" alt="Captured Image">


    <script>
        // Start webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                document.getElementById('video').srcObject = stream;
            })
            .catch(error => console.error("Error accessing webcam:", error));

        function takeSnapshot() {
            let video = document.getElementById('video');
            let canvas = document.getElementById('canvas');
            let context = canvas.getContext('2d');
            
            // Draw image from video
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            let imageData = canvas.toDataURL('image/png'); // Convert to base64
            
            // Show preview
            document.getElementById('snapshot').src = imageData;
        }

        function uploadImage() {
            let canvas = document.getElementById('canvas');
            let imageData = canvas.toDataURL('image/png'); 

            fetch('../../backend/upload_photo.php', {
                method: 'POST',
                body: JSON.stringify({ image: imageData }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.text())
            .then(data => alert(data))
            .catch(error => console.error("Error uploading:", error));
        }
    </script>

<?php
    require '../../db/db_connect.php';
    $sql = "SELECT avatar FROM users WHERE email = '$email'"; // Change 'images_table' and 'id' accordingly
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageUrl = $row["avatar"];
    } else {
        echo "No image found!";
    }

?>
    <?php if (isset($imageUrl)): ?>
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Image">
    <?php endif; ?>
        </main>

        <footer>
        <?php include '../../_footer.php'?>
        </footer>
    </body>