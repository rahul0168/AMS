<?php
require_once "db.php";
session_start();


function create_server_session($pdo, $session_id, $user_id) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $stmt = $pdo->prepare("REPLACE INTO sessions (id, user_id, ip_address, user_agent, data, last_activity) VALUES (:id, :user_id, :ip, :ua, '', NOW())");
    $stmt->execute([
        ':id' => $session_id,
        ':user_id' => $user_id,
        ':ip' => $ip,
        ':ua' => $ua
    ]);
}


function register_user($pdo, $name, $email, $password, $role = 'viewer', $department_id = null) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, department_id) VALUES (:name, :email, :hash, :role, :dept)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':hash' => $hash,
        ':role' => $role,
        ':dept' => $department_id
    ]);
    return $pdo->lastInsertId();
}


function login_user($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, department_id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'department_id' => $user['department_id']
        ];

        create_server_session($pdo, session_id(), $user['id']);
        return true;
    }
    return false;
}


function logout_user($pdo = null) {
    if ($pdo && session_id()) {
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = :id");
        $stmt->execute([':id' => session_id()]);
    }
    // Clear session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        setcookie(session_name(), '', time() - 42000, "/");
    }
    session_destroy();
}


function current_user() {
    return $_SESSION['user'] ?? null;
}


function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}
