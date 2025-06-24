<?php
// api/cordenador/grade_create.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$turma_id     = (int)($_POST['turma_id']     ?? 0);
$dia_semana   = (int)($_POST['dia_semana']   ?? 0);
$posicao      = (int)($_POST['posicao']      ?? 0);
$id_disciplina= (int)($_POST['id_disciplina']?? 0);
$sala         = trim($_POST['sala']         ?? '');
$cor_evento   = trim($_POST['cor_evento']   ?? '#CCCCCC');
$id_professor = (int)($_POST['id_professor']?? 0);

if (!$turma_id||!$dia_semana||!$posicao||!$id_disciplina||!$id_professor) {
    http_response_code(400);
    exit(json_encode(['error'=>'Parâmetros obrigatórios faltando'], JSON_UNESCAPED_UNICODE));
}

// Descobrimos id_divisao (supondo divisão A padrão, adapte conforme seu modelo)
$sql = "SELECT id_divisao FROM divisoes WHERE id_turma=? ORDER BY nome_divisao LIMIT 1";
$stmt= $mysqli->prepare($sql);
$stmt->bind_param('i',$turma_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$id_divisao = $row ? (int)$row['id_divisao'] : 0;
if (!$id_divisao) {
    http_response_code(400);
    exit(json_encode(['error'=>'Divisão não encontrada'], JSON_UNESCAPED_UNICODE));
}

$sql = "
  INSERT INTO grade_aulas
    (id_turma,id_divisao,posicao_aula,id_disciplina,id_professor,sala,dia_semana,cor_evento)
  VALUES (?,?,?,?,?,?,?,?)
";
$stmt = $mysqli->prepare($sql);
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
    exit(json_encode(['error'=>$stmt->error], JSON_UNESCAPED_UNICODE));
}

echo json_encode(['success'=>true,'id_grade'=>$stmt->insert_id], JSON_UNESCAPED_UNICODE);
