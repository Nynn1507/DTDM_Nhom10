<?php
// generate_fake_data.php
require_once __DIR__ . '/../config/database.php';

function randomString($length = 8) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

function randomName() {
    $first = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
    $middle = ['Văn', 'Thị', 'Hữu', 'Đức', 'Minh', 'Thanh', 'Ngọc', 'Quang', 'Tuấn', 'Hải'];
    $last = ['Anh', 'Bình', 'Cường', 'Dũng', 'Đạt', 'Hà', 'Huy', 'Khang', 'Linh', 'Mai', 'Nam', 'Phong', 'Quân', 'Sơn', 'Tú', 'Vinh', 'Xuân'];
    return $first[array_rand($first)] . ' ' . $middle[array_rand($middle)] . ' ' . $last[array_rand($last)];
}

function randomCode($prefix, $length = 8) {
    return $prefix . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

set_time_limit(600);
$pdo->beginTransaction();

try {
    // 1. ACCOUNT (5300)
    echo "Đang tạo ACCOUNT...\n";
    $pdo->prepare("INSERT IGNORE INTO ACCOUNT (username, password, role) VALUES ('admin', ?, 'admin')")
        ->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
    $adminId = $pdo->query("SELECT id FROM ACCOUNT WHERE username = 'admin'")->fetchColumn();

    $lecturerIds = [];
    for ($i = 1; $i <= 300; $i++) {
        $pdo->prepare("INSERT INTO ACCOUNT (username, password, role) VALUES (?, ?, 'lecturer')")
            ->execute(['lecturer' . $i, password_hash('123456', PASSWORD_DEFAULT)]);
        $lecturerIds[] = $pdo->lastInsertId();
    }

    $studentIds = [];
    for ($i = 1; $i <= 5000; $i++) {
        $pdo->prepare("INSERT INTO ACCOUNT (username, password, role) VALUES (?, ?, 'student')")
            ->execute(['student' . $i, password_hash('123456', PASSWORD_DEFAULT)]);
        $studentIds[] = $pdo->lastInsertId();
    }
    echo "Đã tạo ACCOUNT: " . (1 + 300 + 5000) . "\n";

    // 2. CLASS (100)
    echo "Đang tạo CLASS...\n";
    $classIds = [];
    $prefixes = ['CNTT', 'KT', 'QTKD', 'NN', 'XH', 'KHMT', 'KTPM', 'TMDT'];
    for ($i = 1; $i <= 100; $i++) {
        $className = $prefixes[array_rand($prefixes)] . '-K' . rand(42, 48) . strtoupper(randomString(1));
        $pdo->prepare("INSERT INTO CLASS (class_name) VALUES (?)")->execute([$className]);
        $classIds[] = $pdo->lastInsertId();
    }

    // 3. STUDENT (5000)
    echo "Đang tạo STUDENT...\n";
    $studentAccountIds = array_slice($studentIds, 0, 5000);
    $i = 1;
    foreach ($studentAccountIds as $accId) {
        $classId = $classIds[array_rand($classIds)];
        $fullName = randomName();
        $code = randomCode('SV', 5);
        $email = strtolower(str_replace(' ', '.', $fullName)) . '@student.edu.vn';
        $pdo->prepare("INSERT INTO STUDENT (account_id, student_code, full_name, class_id, email) VALUES (?, ?, ?, ?, ?)")
            ->execute([$accId, $code, $fullName, $classId, $email]);
        if ($i % 500 == 0) echo "Đã chèn $i sinh viên\n";
        $i++;
    }

    // 4. LECTURER (300)
    echo "Đang tạo LECTURER...\n";
    $lecturerAccountIds = array_slice($lecturerIds, 0, 300);
    $i = 1;
    foreach ($lecturerAccountIds as $accId) {
        $fullName = randomName();
        $code = randomCode('GV', 5);
        $faculty = ['Công nghệ thông tin', 'Kinh tế', 'Quản trị kinh doanh', 'Ngoại ngữ', 'Khoa học xã hội', 'Kỹ thuật'][array_rand([0,1,2,3,4,5])];
        $email = strtolower(str_replace(' ', '.', $fullName)) . '@teacher.edu.vn';
        $pdo->prepare("INSERT INTO LECTURER (account_id, lecturer_code, full_name, faculty, email) VALUES (?, ?, ?, ?, ?)")
            ->execute([$accId, $code, $fullName, $faculty, $email]);
        if ($i % 50 == 0) echo "Đã chèn $i giảng viên\n";
        $i++;
    }

    // 5. COURSE (200)
    echo "Đang tạo COURSE...\n";
    $courseIds = [];
    $courseNames = ['Lập trình Web', 'Cơ sở dữ liệu', 'Mạng máy tính', 'Trí tuệ nhân tạo', 'Hệ điều hành',
                    'Kỹ thuật phần mềm', 'An toàn thông tin', 'Điện toán đám mây', 'Phân tích dữ liệu', 'Học máy'];
    for ($i = 1; $i <= 200; $i++) {
        $code = 'MH' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $name = $courseNames[array_rand($courseNames)] . ' ' . rand(1, 9);
        $credits = rand(2, 4);
        $lecturerId = $lecturerAccountIds[array_rand($lecturerAccountIds)];
        $desc = 'Mô tả môn học ' . $name;
        $pdo->prepare("INSERT INTO COURSE (course_code, course_name, credits, lecturer_id, description) VALUES (?, ?, ?, ?, ?)")
            ->execute([$code, $name, $credits, $lecturerId, $desc]);
        $courseIds[] = $pdo->lastInsertId();
    }

    // 6. ENROLLMENT (25000)
    echo "Đang tạo ENROLLMENT...\n";
    $semesters = ['2023.1', '2023.2', '2024.1', '2024.2'];
    $years = [2023, 2024];
    $studentIds = array_column($pdo->query("SELECT account_id FROM STUDENT")->fetchAll(), 'account_id');
    for ($i = 0; $i < 25000; $i++) {
        $studentId = $studentIds[array_rand($studentIds)];
        $courseId = $courseIds[array_rand($courseIds)];
        $semester = $semesters[array_rand($semesters)];
        $year = $years[array_rand($years)];
        $pdo->prepare("INSERT IGNORE INTO ENROLLMENT (student_id, course_id, semester, year) VALUES (?, ?, ?, ?)")
            ->execute([$studentId, $courseId, $semester, $year]);
        if ($i % 1000 == 0) echo "Đã chèn $i đăng ký\n";
    }

    // 7. SECTION (2000)
    echo "Đang tạo SECTION...\n";
    $sectionIds = [];
    for ($i = 1; $i <= 2000; $i++) {
        $courseId = $courseIds[array_rand($courseIds)];
        $classId = $classIds[array_rand($classIds)];
        $lecturerId = $pdo->query("SELECT lecturer_id FROM COURSE WHERE id = $courseId")->fetchColumn();
        $date = date('Y-m-d', strtotime('+' . rand(0, 365) . ' days', strtotime('2024-01-01')));
        $time = ['Sáng (7h30-9h30)', 'Sáng (9h45-11h45)', 'Chiều (13h30-15h30)', 'Chiều (15h45-17h45)'][array_rand([0,1,2,3])];
        $status = ['active', 'expired', 'completed'][array_rand([0,1,2])];
        $pdo->prepare("INSERT INTO SECTION (course_id, class_id, lecturer_id, session_date, session_time, status) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$courseId, $classId, $lecturerId, $date, $time, $status]);
        $sectionIds[] = $pdo->lastInsertId();
        if ($i % 200 == 0) echo "Đã chèn $i buổi học\n";
    }

    // 8. QR_CODE (30000)
    echo "Đang tạo QR_CODE...\n";
    $qrIds = [];
    for ($i = 1; $i <= 30000; $i++) {
        $sectionId = $sectionIds[array_rand($sectionIds)];
        $qrCode = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare("INSERT INTO QR_CODE (section_id, qr_code, expires_at) VALUES (?, ?, ?)")
            ->execute([$sectionId, $qrCode, $expires]);
        $qrIds[] = $pdo->lastInsertId();
        if ($i % 1000 == 0) echo "Đã chèn $i mã QR\n";
    }

    // 9. ATTENDANCE_SESSION (30000)
    echo "Đang tạo ATTENDANCE_SESSION...\n";
    $attSessionIds = [];
    for ($i = 1; $i <= 30000; $i++) {
        $qrId = $qrIds[array_rand($qrIds)];
        $start = date('Y-m-d H:i:s', strtotime('-' . rand(0, 3600) . ' seconds'));
        $end = date('Y-m-d H:i:s', strtotime('+' . rand(600, 3600) . ' seconds', strtotime($start)));
        $status = ['open', 'closed'][array_rand([0,1])];
        $pdo->prepare("INSERT INTO ATTENDANCE_SESSION (qr_code_id, start_time, end_time, status) VALUES (?, ?, ?, ?)")
            ->execute([$qrId, $start, $end, $status]);
        $attSessionIds[] = $pdo->lastInsertId();
        if ($i % 1000 == 0) echo "Đã chèn $i phiên điểm danh\n";
    }

    // 10. ATTENDANCE (3.000.000)
    echo "Đang tạo ATTENDANCE (3 triệu dòng)...\n";
    $studentsByClass = [];
    $stmt = $pdo->query("SELECT account_id, class_id FROM STUDENT");
    while ($row = $stmt->fetch()) {
        $studentsByClass[$row['class_id']][] = $row['account_id'];
    }

    $statuses = ['present', 'absent', 'late'];
    $inserted = 0;

    foreach ($attSessionIds as $attSessionId) {
        $qrId = $pdo->query("SELECT qr_code_id FROM ATTENDANCE_SESSION WHERE id = $attSessionId")->fetchColumn();
        $sectionId = $pdo->query("SELECT section_id FROM QR_CODE WHERE id = $qrId")->fetchColumn();
        $classId = $pdo->query("SELECT class_id FROM SECTION WHERE id = $sectionId")->fetchColumn();
        
        if (!isset($studentsByClass[$classId])) continue;
        $studentList = $studentsByClass[$classId];
        $presentCount = rand(round(count($studentList)*0.8), count($studentList));
        $selectedKeys = (array) array_rand($studentList, $presentCount);
        $selectedStudents = array_intersect_key($studentList, array_flip($selectedKeys));
        
        $values = [];
        foreach ($selectedStudents as $studentId) {
            $status = $statuses[array_rand($statuses)];
            $scannedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 600) . ' seconds'));
            $values[] = "($attSessionId, $studentId, '$status', '$scannedAt')";
        }
        if (empty($values)) continue;

        $sql = "INSERT INTO ATTENDANCE (attendance_session_id, student_id, status, scanned_at) VALUES " . implode(',', $values);
        $pdo->exec($sql);
        $inserted += count($values);
        if ($inserted % 10000 == 0) echo "Đã chèn $inserted bản ghi ATTENDANCE\n";
    }

    $pdo->commit();
    echo "Hoàn thành! Tổng số ATTENDANCE đã chèn: $inserted\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Lỗi: " . $e->getMessage() . "\n";
}
