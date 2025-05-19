<?php
$conn = new mysqli('localhost', 'root', '', 'casestudy_db');
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}
// echo 'Kết nối thành công';
?>
