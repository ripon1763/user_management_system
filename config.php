<?php
$host = 'localhost';
$db_name = 'user_management';
$db_username = 'root';
$db_password = '';

$pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_username, $db_password);
?>
