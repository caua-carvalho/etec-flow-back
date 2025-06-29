<?php
// api/cordenador/grade/create.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

// lê e sanitiza
$turma_id      = (int)($_POST['turma_id']      ?? 0);
$dia_semana    = (int)($_POST['dia_semana']    ?? 0);
$posicao       = (int)($_POST['posicao']       ?? 0);
$id_divisao    = (int)($_POST['id_divisao']    ?? 0);
$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);
$id_professor  = (int)($_POST['id_professor']  ?? 0);
$sala          = trim($_POST['sala']           ?? '');

// valida obrigatórios
$missing = [];
if (!$turma_id)       $missing[] = 'turma_id';
if (!$dia_semana)     $missing[] = 'dia_semana';
if (!$posicao)        $missing[] = 'posicao';
if (!$id_divisao)     $missing[] = 'id_divisao';
if (!$id_disciplina)  $missing[] = 'id_disciplina';
if (!$id_professor)   $missing[] = 'id_professor';

if ($missing) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Parâmetros faltando: '.implode(', ', $missing)
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// verifica se o professor já tem aula nesse dia/horário
$chk = $mysqli->prepare("
  SELECT 1
    FROM grade_aulas
   WHERE id_professor = ?
     AND dia_semana   = ?
     AND posicao_aula = ?
   LIMIT 1
");
$chk->bind_param('iii', $id_professor, $dia_semana, $posicao);
$chk->execute();
if ($chk->get_result()->fetch_assoc()) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Professor já possui aula nesse dia e horário'
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// prepara INSERT (7 colunas: turma, divisao, posicao, disciplina, professor, sala, dia_semana)
$stmt = $mysqli->prepare("
  INSERT INTO grade_aulas
    (id_turma, id_divisao, posicao_aula, id_disciplina, id_professor, sala, dia_semana)
  VALUES (?,?,?,?,?,?,?)
");
$stmt->bind_param(
  'iiiiisi',
  $turma_id,
  $id_divisao,
  $posicao,
  $id_disciplina,
  $id_professor,
  $sala,
  $dia_semana
);

try {
  $stmt->execute();
  echo json_encode([
    'success'  => true,
    'id_grade' => $stmt->insert_id
  ], JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
  // trata duplicidade na chave única uc_grade_divisao_posicao
  if ($mysqli->errno === 1062) {
    http_response_code(400);
    echo json_encode([
      'error' => 'Já existe aula nessa divisão e horário'
    ], JSON_UNESCAPED_UNICODE);
  } else {
    http_response_code(500);
    echo json_encode([
      'error' => 'Erro no banco: '.$e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
  }
}
exit;
