<?php
// api/coordinator/courses.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

$escola_id      = isset($_GET['escola_id'])      ? (int) $_GET['escola_id']      : 0;
$coordenador_id = isset($_GET['coordenador_id']) ? (int) $_GET['coordenador_id'] : 0;

if (!$escola_id || !$coordenador_id) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Faltando parâmetro: escola_id e coordenador_id são obrigatórios'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT 
        c.id_curso,
        c.nome
      FROM cursos AS c
      JOIN coordenador_cursos AS cc
        ON cc.id_curso = c.id_curso
     WHERE c.id_escola      = ?
       AND cc.id_coordenador = ?
     ORDER BY c.nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $escola_id, $coordenador_id);
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
