<?php
// api/cordenador/grade/disciplina_turma.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
if (!$turma_id) {
  http_response_code(400);
  echo json_encode(['error'=>'Faltando parâmetro turma_id'], JSON_UNESCAPED_UNICODE);
  exit;
}

// pega id_curso da turma
$sql = "SELECT id_curso FROM turmas WHERE id_turma = ? LIMIT 1";
$stmt= $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) {
  http_response_code(404);
  echo json_encode(['error'=>'Turma não encontrada'], JSON_UNESCAPED_UNICODE);
  exit;
}
$id_curso = (int)$row['id_curso'];

// lista disciplinas do curso
$sql = "
  SELECT
    id_disciplina AS id,
    nome,
    cor_evento    AS cor
    FROM disciplinas
   WHERE id_curso = ?
   ORDER BY nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id_curso);
$stmt->execute();
$res = $stmt->get_result();

$disc = [];
while ($d = $res->fetch_assoc()) {
  $disc[] = $d;
}

echo json_encode(['disciplinas'=>$disc], JSON_UNESCAPED_UNICODE);
