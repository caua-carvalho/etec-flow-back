<?php
// api/coordinator/disciplinas_delete.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$id             = isset($_GET['id_disciplina'])   ? (int)$_GET['id_disciplina']   : 0;
$coordenador_id = isset($_GET['coordenador_id'])  ? (int)$_GET['coordenador_id']  : 0;
if (!$id || !$coordenador_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros faltando'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Deleta apenas se for disciplina de curso do coordenador
$sql = "
  DELETE d
    FROM disciplinas d
    JOIN coordenador_cursos cc ON cc.id_curso = d.id_curso
   WHERE d.id_disciplina = ?
     AND cc.id_coordenador = ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $id, $coordenador_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
