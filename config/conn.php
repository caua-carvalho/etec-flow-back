<?php
// config/db.php
header('Content-Type: application/json; charset=UTF-8');

$DB_HOST = 'localhost';
$DB_USER = 'seu_usuario';
$DB_PASS = 'sua_senha';
$DB_NAME = 'seu_banco';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit(json_encode(['error' => 'Falha na conexão com o banco: ' . $mysqli->connect_error]));
}
$mysqli->set_charset('utf8mb4');
