<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$turma_id      = (int)($_POST['turma_id'] ?? 0);
$dia_semana    = (int)($_POST['dia_semana'] ?? 0);
$posicao       = (int)($_POST['posicao'] ?? 0);
$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);
$id_professor  = (int)($_POST['id_professor'] ?? 0);
$id_divisao    = (int)($_POST['id_divisao'] ?? 0);
$sala          = trim($_POST['sala'] ?? '');
$cor_evento    = trim($_POST['cor_evento'] ?? '#CCCCCC');

$missing = [];
if (!$turma_id)      $missing[] = 'turma_id';
if (!$dia_semana)    $missing[] = 'dia_semana';
if (!$posicao)       $missing[] = 'posicao';
if (!$id_disciplina)$missing[] = 'id_disciplina';
if (!$id_professor) $missing[] = 'id_professor';
if (!$id_divisao)   $missing[] = 'id_divisao';

if ($missing) {
  http_response_code(400);
  exit(json_encode(['error'=>'Parâmetros faltando: '.implode(', ',$missing)], JSON_UNESCAPED_UNICODE));
}

$stmt = $mysqli->prepare("
  INSERT INTO grade_aulas
    (id_turma,id_divisao,posicao_aula,id_disciplina,id_professor,sala,dia_semana,cor_evento)
  VALUES (?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
  'iiiiisis',
  $turma_id,
  $id_divisao,
  $posicao,
  $id_disciplina,
  $id_professor,
  $sala,
  $dia_semana,
  $cor_evento
);

try {
  $stmt->execute();
  // sucesso
  echo json_encode([
    'success'  => true,
    'id_grade' => $stmt->insert_id
  ], JSON_UNESCAPED_UNICODE);
} catch (mysqli_sql_exception $e) {
  // erro de duplicidade de divisão+posição
  if ($mysqli->errno === 1062) {
    http_response_code(400);
    echo json_encode([
      'error' => 'Já existe uma aula cadastrada nessa divisão e horário'
    ], JSON_UNESCAPED_UNICODE);
  } else {
    http_response_code(500);
    echo json_encode([
      'error' => 'Erro no banco: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
  }
}
exit;