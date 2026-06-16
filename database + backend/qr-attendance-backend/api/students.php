<?php
// api/students.php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['admin']);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Lấy danh sách sinh viên (có thể thêm filter)
        $stmt = $pdo->query("
            SELECT s.*, c.class_name 
            FROM STUDENT s 
            LEFT JOIN CLASS c ON s.class_id = c.id
        ");
        sendJSON($stmt->fetchAll());
        break;

    case 'POST':
        // Thêm sinh viên (cần tạo account trước)
        $data = json_decode(file_get_contents('php://input'), true);
        // Validate và chèn
        // (Viết chi tiết tùy nhu cầu)
        sendJSON(['message' => 'Thêm sinh viên thành công']);
        break;

    case 'PUT':
        // Cập nhật
        break;

    case 'DELETE':
        // Xóa
        break;

    default:
        sendJSON(['error' => 'Method not allowed'], 405);
}
?>