<?php
// config/db.php
header('Content-Type: application/json; charset=UTF-8');

$DB_HOST = 'sql113.infinityfree.com';
$DB_USER = 'if0_39241532';
$DB_PASS = 'vYm6EJTK2v';
$DB_NAME = 'if0_39241532_etec_flow';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit(json_encode(['error' => 'Falha na conexão com o banco: ' . $mysqli->connect_error]));
}
$mysqli->set_charset('utf8mb4');
