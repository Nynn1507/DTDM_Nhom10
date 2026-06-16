<?php
// helpers/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['account_id']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE id = ?");
    $stmt->execute([$_SESSION['account_id']]);
    return $stmt->fetch();
}

function requireRole($roles = []) {
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], $roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    return $user;
}

function generateQRCode($length = 32) {
    return bin2hex(random_bytes($length));
}
?>