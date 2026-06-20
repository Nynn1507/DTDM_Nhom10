<?php
// api/profile.php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['admin', 'lecturer', 'student']);

if ($user['role'] === 'student') {
    $stmt = $pdo->prepare("
        SELECT s.*, c.class_name
        FROM STUDENT s
        LEFT JOIN CLASS c ON s.class_id = c.id
        WHERE s.account_id = ?
    ");
    $stmt->execute([$user['id']]);
    $info = $stmt->fetch();
} elseif ($user['role'] === 'lecturer') {
    $stmt = $pdo->prepare("SELECT * FROM LECTURER WHERE account_id = ?");
    $stmt->execute([$user['id']]);
    $info = $stmt->fetch();
} else {
    $info = null;
}

sendJSON([
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'info' => $info
]);
?>
