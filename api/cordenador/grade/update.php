<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$id_grade      = (int)($_POST['id_grade'] ?? 0);
$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);
$id_professor  = (int)($_POST['id_professor'] ?? 0);
$id_divisao    = (int)($_POST['id_divisao'] ?? 0);
$sala          = trim($_POST['sala'] ?? '');
$cor_evento    = trim($_POST['cor_evento'] ?? '');

$missing = [];
if (!$id_grade)      $missing[] = 'id_grade';
if (!$id_disciplina)$missing[] = 'id_disciplina';
if (!$id_professor) $missing[] = 'id_professor';
if (!$id_divisao)   $missing[] = 'id_divisao';

if ($missing) {
  http_response_code(400);
  exit(json_encode(['error'=>'Parâmetros faltando: '.implode(', ',$missing)], JSON_UNESCAPED_UNICODE));
}

$sql = "
  UPDATE grade_aulas
     SET id_disciplina=?, id_professor=?, id_divisao=?, sala=?, cor_evento=?
   WHERE id_grade=?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
  'iiissi',
  $id_disciplina,
  $id_professor,
  $id_divisao,
  $sala,
  $cor_evento,
  $id_grade
);
if (!$stmt->execute()) {
  http_response_code(500);
  exit(json_encode(['error'=>$stmt->error], JSON_UNESCAPED_UNICODE));
}

echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
exit;
