<?php
session_start();

// Only destroy the session if explicitly requested
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    session_destroy();
}
?>
