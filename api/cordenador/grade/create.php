<?php
// api/cordenador/grade/create.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

// fetch and sanitize
$turma_id      = (int) ($_POST['turma_id']      ?? 0);
$dia_semana    = (int) ($_POST['dia_semana']    ?? 0);
$posicao       = (int) ($_POST['posicao']       ?? 0);
$id_disciplina = (int) ($_POST['id_disciplina'] ?? 0);
$id_professor  = (int) ($_POST['id_professor']  ?? 0);
$sala          = trim($_POST['sala']           ?? '');
$cor_evento    = trim($_POST['cor_evento']     ?? '#CCCCCC');

// validation
$errors = [];
if (!$turma_id)      $errors[] = 'turma_id';
if (!$dia_semana)    $errors[] = 'dia_semana';
if (!$posicao)       $errors[] = 'posicao';
if (!$id_disciplina) $errors[] = 'id_disciplina';
if (!$id_professor)  $errors[] = 'id_professor';

if ($errors) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Parâmetros faltando: ' . implode(', ', $errors)
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// lookup a division (adjust if you need a specific one)
$stmt = $mysqli->prepare(
  "SELECT id_divisao
     FROM divisoes
    WHERE id_turma = ?
    LIMIT 1"
);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$id_divisao = $row['id_divisao'] ?? 0;
if (!$id_divisao) {
  http_response_code(400);
  echo json_encode(['error'=>'Divisão não encontrada'], JSON_UNESCAPED_UNICODE);
  exit;
}

// insert
$stmt = $mysqli->prepare("
  INSERT INTO grade_aulas
    (id_turma,id_divisao,posicao_aula,id_disciplina,id_professor,sala,dia_semana,cor_evento)
  VALUES (?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
  'iiiissis',
  $turma_id,
  $id_divisao,
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

// success
echo json_encode([
  'success'  => true,
  'id_grade' => $stmt->insert_id
], JSON_UNESCAPED_UNICODE);
