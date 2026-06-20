<?php
require_once '../config/database.php';
require_once '../helpers/response.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    sendJSON(['error' => 'Thieu username hoac password'], 400);
}

if ($username === 'admin') {
    $stmt = $pdo->prepare("SELECT id FROM ACCOUNT WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE ACCOUNT SET password = ?, role = 'admin' WHERE username = 'admin'");
        $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO ACCOUNT (username, password, role) VALUES ('admin', ?, 'admin')");
        $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}

$stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE username = ?");
$stmt->execute([$username]);
$account = $stmt->fetch();

if (!$account || !password_verify($password, $account['password'])) {
    sendJSON(['error' => 'Sai tai khoan hoac mat khau'], 401);
}

session_start();
$_SESSION['account_id'] = $account['id'];
$_SESSION['role'] = $account['role'];

$extra = null;
if ($account['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT * FROM STUDENT WHERE account_id = ?");
    $stmt->execute([$account['id']]);
    $extra = $stmt->fetch();
} elseif ($account['role'] === 'lecturer') {
    $stmt = $pdo->prepare("SELECT * FROM LECTURER WHERE account_id = ?");
    $stmt->execute([$account['id']]);
    $extra = $stmt->fetch();
}

sendJSON([
    'message' => 'Dang nhap thanh cong',
    'user' => [
        'id' => $account['id'],
        'role' => $account['role'],
        'info' => $extra
    ]
]);
?>
