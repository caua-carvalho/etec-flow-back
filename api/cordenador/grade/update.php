<?php
// api/cordenador/grade/update.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

$id_grade      = (int)($_POST['id_grade']      ?? 0);
$id_divisao    = (int)($_POST['id_divisao']    ?? 0);
$id_disciplina= (int)($_POST['id_disciplina'] ?? 0);
$id_professor = (int)($_POST['id_professor']  ?? 0);
$sala          = trim($_POST['sala']           ?? '');

// checa obrigatórios
$missing = [];
if (!$id_grade)       $missing[] = 'id_grade';
if (!$id_divisao)     $missing[] = 'id_divisao';
if (!$id_disciplina)  $missing[] = 'id_disciplina';
if (!$id_professor)   $missing[] = 'id_professor';
if ($missing) {
  http_response_code(400);
  echo json_encode(['error'=>'Parâmetros faltando: '.implode(', ',$missing)], JSON_UNESCAPED_UNICODE);
  exit;
}

$sql = "
  UPDATE grade_aulas
     SET id_divisao   = ?,
         id_disciplina= ?,
         id_professor = ?,
         sala         = ?
   WHERE id_grade = ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
  'iii si',
  $id_divisao,
  $id_disciplina,
  $id_professor,
  $sala,
  $id_grade
);

try {
  $stmt->execute();
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
} catch (mysqli_sql_exception $e) {
  if ($mysqli->errno === 1062) {
    http_response_code(400);
    echo json_encode(['error'=>'Já existe aula nessa divisão e horário'], JSON_UNESCAPED_UNICODE);
  } else {
    http_response_code(500);
    echo json_encode(['error'=>'Erro no banco: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
  }
}
