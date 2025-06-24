<?php
// api/cordenador/grade/delete.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

$id_grade = (int)($_POST['id_grade'] ?? 0);
if (!$id_grade) {
  http_response_code(400);
  echo json_encode(['error'=>'Parâmetro id_grade faltando'], JSON_UNESCAPED_UNICODE);
  exit;
}

$stmt = $mysqli->prepare("DELETE FROM grade_aulas WHERE id_grade = ?");
$stmt->bind_param('i', $id_grade);

if ($stmt->execute()) {
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(['error'=>'Erro no banco: '.$stmt->error], JSON_UNESCAPED_UNICODE);
}
