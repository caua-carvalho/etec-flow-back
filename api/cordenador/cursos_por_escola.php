<?php
// api/coordinator/courses.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

$escola_id = isset($_GET['escola_id']) 
    ? (int) $_GET['escola_id'] 
    : 0;

if (!$escola_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: escola_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT 
        id_curso,
        nome
      FROM cursos
     WHERE id_escola = ?
     ORDER BY nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $escola_id);
$stmt->execute();
$result = $stmt->get_result();

$cursos = [];
while ($row = $result->fetch_assoc()) {
    $cursos[] = [
        'id'   => (int)$row['id_curso'],
        'nome' => $row['nome'],
    ];
}

echo json_encode(['cursos' => $cursos], JSON_UNESCAPED_UNICODE);
