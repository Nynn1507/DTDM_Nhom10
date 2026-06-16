<?php
// api/attendance.php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['student', 'lecturer']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Sinh viên scan QR
    if ($user['role'] !== 'student') {
        sendJSON(['error' => 'Chỉ sinh viên mới được điểm danh'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $qr_code = $data['qr_code'] ?? '';

    if (empty($qr_code)) {
        sendJSON(['error' => 'Thiếu mã QR'], 400);
    }

    // Tìm QR code
    $stmt = $pdo->prepare("
        SELECT q.*, s.course_id, s.class_id, s.session_date, s.session_time,
               c.course_name, cl.class_name
        FROM QR_CODE q
        JOIN SECTION s ON q.section_id = s.id
        JOIN COURSE c ON s.course_id = c.id
        JOIN CLASS cl ON s.class_id = cl.id
        WHERE q.qr_code = ? AND q.expires_at > NOW()
    ");
    $stmt->execute([$qr_code]);
    $qr = $stmt->fetch();
    if (!$qr) {
        sendJSON(['error' => 'Mã QR không hợp lệ hoặc đã hết hạn'], 404);
    }

    // Kiểm tra sinh viên thuộc lớp đó
    $stmt = $pdo->prepare("SELECT * FROM STUDENT WHERE account_id = ? AND class_id = ?");
    $stmt->execute([$user['id'], $qr['class_id']]);
    if (!$stmt->fetch()) {
        sendJSON(['error' => 'Bạn không thuộc lớp này'], 403);
    }

    // Tạo hoặc lấy attendance session
    $stmt = $pdo->prepare("
        SELECT id FROM ATTENDANCE_SESSION WHERE qr_code_id = ? AND status = 'open'
    ");
    $stmt->execute([$qr['id']]);
    $attSession = $stmt->fetch();
    if (!$attSession) {
        // Tạo mới attendance session
        $stmt = $pdo->prepare("
            INSERT INTO ATTENDANCE_SESSION (qr_code_id, start_time, status)
            VALUES (?, NOW(), 'open')
        ");
        $stmt->execute([$qr['id']]);
        $attSessionId = $pdo->lastInsertId();
    } else {
        $attSessionId = $attSession['id'];
    }

    // Kiểm tra đã điểm danh chưa
    $stmt = $pdo->prepare("SELECT * FROM ATTENDANCE WHERE attendance_session_id = ? AND student_id = ?");
    $stmt->execute([$attSessionId, $user['id']]);
    if ($stmt->fetch()) {
        sendJSON(['error' => 'Bạn đã điểm danh buổi này'], 409);
    }

    // Ghi điểm danh
    $stmt = $pdo->prepare("
        INSERT INTO ATTENDANCE (attendance_session_id, student_id, status, scanned_at)
        VALUES (?, ?, 'present', NOW())
    ");
    $stmt->execute([$attSessionId, $user['id']]);

    sendJSON([
        'message' => 'Điểm danh thành công',
        'course_name' => $qr['course_name'],
        'class_name'  => $qr['class_name'],
        'date'        => $qr['session_date'],
        'time'        => $qr['session_time']
    ]);
}

if ($method === 'GET') {
    // Lấy lịch sử điểm danh của sinh viên
    if ($user['role'] !== 'student') {
        sendJSON(['error' => 'Chỉ sinh viên mới xem lịch sử'], 403);
    }

    $stmt = $pdo->prepare("
        SELECT a.*, s.session_date, s.session_time, c.course_name
        FROM ATTENDANCE a
        JOIN ATTENDANCE_SESSION att ON a.attendance_session_id = att.id
        JOIN QR_CODE q ON att.qr_code_id = q.id
        JOIN SECTION s ON q.section_id = s.id
        JOIN COURSE c ON s.course_id = c.id
        WHERE a.student_id = ?
        ORDER BY a.scanned_at DESC
    ");
    $stmt->execute([$user['id']]);
    sendJSON($stmt->fetchAll());
}
?>