<?php
// api/logout.php
session_start();
session_destroy();
sendJSON(['message' => 'Đăng xuất thành công']);
?>