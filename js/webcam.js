let video = document.getElementById('video');
let avatar = document.getElementById('avatar');
let canvas = document.getElementById('canvas');
let openCameraBtn = document.getElementById('openCamera');
let captureBtn = document.getElementById('capture');
let uploadBtn = document.getElementById('upload');
let imageDataInput = document.getElementById('imageData');

let stream = null;

// Open Camera
openCameraBtn.addEventListener('click', async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = 'block';
        avatar.style.display = 'none';
        captureBtn.style.display = 'inline-block';
    } catch (error) {
        console.error("Error accessing webcam:", error);
    }
});

// Capture Image
captureBtn.addEventListener('click', () => {
    let context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    let imageData = canvas.toDataURL("image/png");
    avatar.src = imageData;
    avatar.style.display = 'block';
    video.style.display = 'none';
    imageDataInput.value = imageData;
    uploadBtn.style.display = 'inline-block';

    // Stop video stream
    stream.getTracks().forEach(track => track.stop());
});
