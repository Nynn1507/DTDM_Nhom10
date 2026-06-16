<?php
// api/statistics.php
require_once '../config/database.php';
require_once '../helpers/auth.php';
require_once '../helpers/response.php';

$user = requireRole(['admin']);

$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) FROM STUDENT");
$stats['totalStudents'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM LECTURER");
$stats['totalTeachers'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM COURSE");
$stats['totalCourses'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM SECTION");
$stats['totalSessions'] = $stmt->fetchColumn();

// Tỷ lệ điểm danh (có thể tính phức tạp hơn)
$stmt = $pdo->query("SELECT COUNT(*) FROM ATTENDANCE");
$stats['totalAttendance'] = $stmt->fetchColumn();

sendJSON($stats);
?>