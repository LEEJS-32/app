<?php
include __DIR__ . '/../_header.php';  // Go up one directory level
?>

<div class="container">
    <h2>Member Registration</h2>
    <form id="registrationForm" enctype="multipart/form-data">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Profile Photo:</label>
            <input type="file" name="profile_photo" accept="image/*" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <div id="responseMessage"></div>
</div>

<script>
$(document).ready(function() {
    $('#registrationForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: 'handlers/register-handler.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success) {
                    $('#responseMessage').html('<div class="alert alert-success">' + res.message + '</div>');
                    $('#registrationForm')[0].reset();
                } else {
                    $('#responseMessage').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            },
            error: function() {
                $('#responseMessage').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            },
            contentType: false,
            processData: false
        });
    });
});
</script>

<?php
include __DIR__ . '/../_footer.php';  // Go up one directory level
?>