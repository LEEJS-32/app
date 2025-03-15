<?php

date_default_timezone_set('Asia/Kuala_Lumpur');

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_pwd($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false, $disabled = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $checked = ($id == $value) ? 'checked' : '';
        $disabledAttr = $disabled ? 'disabled' : ''; // Ensure disabled is applied
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $checked $disabledAttr> $text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Security
// ============================================================================

// Global user object
session_start();
$_user = $_SESSION['user'] ?? null;

// Login user
function login($user, $url = '/') {
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
// function logout($url = '/') {
//     unset($_SESSION['user']);
//     redirect($url);
// }

// Authorization
function auth(...$roles) {
    global $_user;

    if ($_user) {
        echo "User is logged in. Role: " . $_user['role'] . "<br>";

        if ($roles) {
            echo "Roles required: ";
            print_r($roles); // Display required roles
            echo "<br>";

            if (in_array($_user['role'], $roles)) {
                echo "✅ User role matches. Access granted.<br>";
                return; // ✅ Allow access
            } else {
                echo "❌ User role does NOT match. Access denied.<br>";
            }
        } else {
            echo "✅ No specific role required. Access granted.<br>";
            return; // ✅ Allow access
        }
    } else {
        echo "❌ User is NOT logged in. Access denied.<br>";
    }

    redirect('/pages/admin/admin_login.php'); 
}
