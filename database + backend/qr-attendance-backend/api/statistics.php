<?php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['admin', 'lecturer']);
$stats = [];

if ($user['role'] === 'admin') {
    $stats['totalStudents'] = $pdo->query("SELECT COUNT(*) FROM STUDENT")->fetchColumn();
    $stats['totalTeachers'] = $pdo->query("SELECT COUNT(*) FROM LECTURER")->fetchColumn();
    $stats['totalCourses'] = $pdo->query("SELECT COUNT(*) FROM COURSE")->fetchColumn();
    $stats['totalSessions'] = $pdo->query("SELECT COUNT(*) FROM SECTION")->fetchColumn();
    $stats['totalAttendance'] = $pdo->query("SELECT COUNT(*) FROM ATTENDANCE")->fetchColumn();

    $stmt = $pdo->query("
        SELECT DATE_FORMAT(a.scanned_at, '%Y-%m') AS month, COUNT(*) AS total
        FROM ATTENDANCE a
        GROUP BY DATE_FORMAT(a.scanned_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $stats['monthly'] = array_reverse($stmt->fetchAll());
    sendJSON($stats);
}

$lecturerId = $user['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM SECTION WHERE lecturer_id = ?");
$stmt->execute([$lecturerId]);
$stats['totalSessions'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id)
    FROM ENROLLMENT e
    JOIN COURSE c ON e.course_id = c.id
    WHERE c.lecturer_id = ?
");
$stmt->execute([$lecturerId]);
$stats['totalStudents'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_attendance
    FROM ATTENDANCE a
    JOIN ATTENDANCE_SESSION ats ON a.attendance_session_id = ats.id
    JOIN QR_CODE q ON ats.qr_code_id = q.id
    JOIN SECTION s ON q.section_id = s.id
    WHERE s.lecturer_id = ?
");
$stmt->execute([$lecturerId]);
$totalAttendance = (int) $stmt->fetchColumn();

$stats['totalAttendance'] = $totalAttendance;
$stats['attendanceRate'] = $stats['totalSessions'] > 0 && $stats['totalStudents'] > 0
    ? round(($totalAttendance / ($stats['totalSessions'] * $stats['totalStudents'])) * 100, 2)
    : 0;

$stmt = $pdo->prepare("
    SELECT c.course_name, COUNT(a.id) AS total
    FROM COURSE c
    LEFT JOIN SECTION s ON c.id = s.course_id
    LEFT JOIN QR_CODE q ON s.id = q.section_id
    LEFT JOIN ATTENDANCE_SESSION ats ON q.id = ats.qr_code_id
    LEFT JOIN ATTENDANCE a ON ats.id = a.attendance_session_id
    WHERE c.lecturer_id = ?
    GROUP BY c.id, c.course_name
    ORDER BY c.course_name
");
$stmt->execute([$lecturerId]);
$stats['byCourse'] = $stmt->fetchAll();

sendJSON($stats);
?>
