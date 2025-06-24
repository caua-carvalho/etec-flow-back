<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

// sanitize inputs…
$turma_id      = (int)($_POST['turma_id']      ?? 0);
$dia_semana    = (int)($_POST['dia_semana']    ?? 0);
$posicao       = (int)($_POST['posicao']       ?? 0);
$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);
$id_professor  = (int)($_POST['id_professor']  ?? 0);
$sala          = trim($_POST['sala']           ?? '');
$cor_evento    = trim($_POST['cor_evento']     ?? '#CCCCCC');

// collect missing
$missing = [];
if (!$turma_id)      $missing[] = 'turma_id';
if (!$dia_semana)    $missing[] = 'dia_semana';
if (!$posicao)       $missing[] = 'posicao';
if (!$id_disciplina) $missing[] = 'id_disciplina';
if (!$id_professor)  $missing[] = 'id_professor';

if (count($missing)) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Parâmetros faltando: '.implode(', ',$missing)
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// find a division
$stmt = $mysqli->prepare(
  "SELECT id_divisao FROM divisoes WHERE id_turma = ? LIMIT 1"
);
$stmt->bind_param('i',$turma_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$id_div = $row['id_divisao'] ?? 0;
if (!$id_div) {
  http_response_code(400);
  echo json_encode(['error'=>'Divisão não encontrada'], JSON_UNESCAPED_UNICODE);
  exit;
}

// do insert
$stmt = $mysqli->prepare("
  INSERT INTO grade_aulas
    (id_turma,id_divisao,posicao_aula,id_disciplina,id_professor,sala,dia_semana,cor_evento)
  VALUES (?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
  'iiiiisis',
  $turma_id,
  $id_div,
  $posicao,
  $id_disciplina,
  $id_professor,
  $sala,
  $dia_semana,
  $cor_evento
);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error'=>$stmt->error], JSON_UNESCAPED_UNICODE);
  exit;
}

// on success, ALWAYS echo JSON!
echo json_encode([
  'success'  => true,
  'id_grade' => $stmt->insert_id
], JSON_UNESCAPED_UNICODE);
exit;
