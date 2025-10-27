<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token_time'] = time();
    }
    return $_SESSION['_csrf_token'];
}

function csrf_input_field() {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verify_csrf_or_die($tokenFromRequest) {
    if (empty($tokenFromRequest) || empty($_SESSION['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $tokenFromRequest)) {
        http_response_code(400);
        die('CSRF verification failed.');
    }
    // Optionally rotate token after successful validation:
    // unset($_SESSION['_csrf_token']);
    return true;
}
