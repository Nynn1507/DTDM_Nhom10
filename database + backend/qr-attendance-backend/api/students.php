<?php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['admin']);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("
            SELECT s.*, c.class_name
            FROM STUDENT s
            LEFT JOIN CLASS c ON s.class_id = c.id
            ORDER BY s.account_id DESC
        ");
        sendJSON($stmt->fetchAll());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $studentCode = trim($data['student_code'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $classId = $data['class_id'] ?? null;
        $className = trim($data['class_name'] ?? '');
        $email = trim($data['email'] ?? '');

        if (!$username || !$password || !$studentCode || !$fullName || (!$classId && !$className)) {
            sendJSON(['error' => 'Thieu thong tin sinh vien'], 400);
        }

        try {
            $pdo->beginTransaction();

            if (!$classId && $className) {
                $stmt = $pdo->prepare("SELECT id FROM CLASS WHERE class_name = ?");
                $stmt->execute([$className]);
                $classId = $stmt->fetchColumn();

                if (!$classId) {
                    $stmt = $pdo->prepare("INSERT INTO CLASS (class_name) VALUES (?)");
                    $stmt->execute([$className]);
                    $classId = $pdo->lastInsertId();
                }
            }

            $stmt = $pdo->prepare("INSERT INTO ACCOUNT (username, password, role) VALUES (?, ?, 'student')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $accountId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO STUDENT (account_id, student_code, full_name, class_id, email)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$accountId, $studentCode, $fullName, $classId, $email]);

            $pdo->commit();
            sendJSON(['message' => 'Them sinh vien thanh cong']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            sendJSON(['error' => 'Username hoac MSSV da ton tai'], 409);
        }
        break;

    default:
        sendJSON(['error' => 'Method not allowed'], 405);
}
?>
