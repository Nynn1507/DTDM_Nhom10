<?php
// api/login.php
require_once '../config/database.php';
require_once '../helpers/response.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    sendJSON(['error' => 'Thiếu username hoặc password'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE username = ?");
$stmt->execute([$username]);
$account = $stmt->fetch();

if (!$account || !password_verify($password, $account['password'])) {
    sendJSON(['error' => 'Sai tài khoản hoặc mật khẩu'], 401);
}

session_start();
$_SESSION['account_id'] = $account['id'];
$_SESSION['role'] = $account['role'];

// Lấy thêm thông tin theo role
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
    'message' => 'Đăng nhập thành công',
    'user' => [
        'id'   => $account['id'],
        'role' => $account['role'],
        'info' => $extra
    ]
]);
?>