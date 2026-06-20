<?php
require_once '../helpers/response.php';

session_start();
session_destroy();

sendJSON(['message' => 'Dang xuat thanh cong']);
?>
