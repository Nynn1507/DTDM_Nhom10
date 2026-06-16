<?php
// api/sessions.php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['lecturer', 'student']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Chỉ lecturer mới được tạo
    if ($user['role'] !== 'lecturer') {
        sendJSON(['error' => 'Chỉ giảng viên mới được tạo buổi học'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $course_id   = $data['course_id'] ?? null;
    $class_id    = $data['class_id'] ?? null;
    $session_date = $data['session_date'] ?? null;
    $session_time = $data['session_time'] ?? null;

    if (!$course_id || !$class_id || !$session_date || !$session_time) {
        sendJSON(['error' => 'Thiếu thông tin buổi học'], 400);
    }

    // Kiểm tra giảng viên có phụ trách môn này không
    $stmt = $pdo->prepare("SELECT * FROM COURSE WHERE id = ? AND lecturer_id = ?");
    $stmt->execute([$course_id, $user['id']]);
    if (!$stmt->fetch()) {
        sendJSON(['error' => 'Bạn không phụ trách môn học này'], 403);
    }

    $qr_code = generateQRCode(32);
    $stmt = $pdo->prepare("
        INSERT INTO SECTION (course_id, class_id, lecturer_id, session_date, session_time, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$course_id, $class_id, $user['id'], $session_date, $session_time]);
    $sectionId = $pdo->lastInsertId();

    // Tạo QR code
    $stmt = $pdo->prepare("INSERT INTO QR_CODE (section_id, qr_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
    $stmt->execute([$sectionId, $qr_code]);

    sendJSON([
        'message' => 'Tạo buổi học thành công',
        'section' => [
            'id'          => $sectionId,
            'course_id'   => $course_id,
            'class_id'    => $class_id,
            'date'        => $session_date,
            'time'        => $session_time,
            'qr_code'     => $qr_code,
            'status'      => 'active'
        ]
    ]);
}

if ($method === 'GET') {
    // Lấy danh sách buổi học hôm nay (cho student)
    if ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT class_id FROM STUDENT WHERE account_id = ?");
        $stmt->execute([$user['id']]);
        $classId = $stmt->fetchColumn();
        if (!$classId) sendJSON(['error' => 'Không tìm thấy lớp'], 404);

        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT s.*, c.course_name, l.full_name AS lecturer_name
            FROM SECTION s
            JOIN COURSE c ON s.course_id = c.id
            JOIN LECTURER l ON s.lecturer_id = l.account_id
            WHERE s.class_id = ? AND s.session_date = ? AND s.status = 'active'
        ");
        $stmt->execute([$classId, $today]);
        sendJSON($stmt->fetchAll());
    } else {
        // Lecturer xem danh sách buổi học của mình
        $stmt = $pdo->prepare("
            SELECT s.*, c.course_name, cl.class_name
            FROM SECTION s
            JOIN COURSE c ON s.course_id = c.id
            JOIN CLASS cl ON s.class_id = cl.id
            WHERE s.lecturer_id = ?
            ORDER BY s.session_date DESC
        ");
        $stmt->execute([$user['id']]);
        sendJSON($stmt->fetchAll());
    }
}
?>