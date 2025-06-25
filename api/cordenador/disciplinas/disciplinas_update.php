<?php
// api/cordenador/disciplinas_update.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_coordenador  = intval($input['cordenador_id']   ?? 0);
$id_disciplina   = intval($input['id_disciplina']    ?? 0);
$nome            = trim($mysqli->real_escape_string($input['nome'] ?? ''));
$abreviacao      = trim($mysqli->real_escape_string($input['abreviacao'] ?? ''));
$cor_evento      = trim($mysqli->real_escape_string($input['cor_evento'] ?? '#CCCCCC'));

if (!$id_coordenador || !$id_disciplina || !$nome) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados incompletos']);
  exit;
}

// verificar disciplina e vínculo via curso
$sqlChk =
  "SELECT 1
   FROM disciplinas d
   JOIN coordenador_cursos cc ON cc.id_curso = d.id_curso
   WHERE cc.id_coordenador = ? AND d.id_disciplina = ?";
$stmtChk = $mysqli->prepare($sqlChk);
$stmtChk->bind_param('ii', $id_coordenador, $id_disciplina);
$stmtChk->execute();
if (!$stmtChk->get_result()->fetch_assoc()) {
  http_response_code(403);
  echo json_encode(['error' => 'Não autorizado']);
  exit;
}

// atualizar
$stmt = $mysqli->prepare(
  "UPDATE disciplinas
   SET nome = ?, abreviacao = ?, cor_evento = ?
   WHERE id_disciplina = ?"
);
$stmt->bind_param('sssi', $nome, $abreviacao, $cor_evento, $id_disciplina);
$ok = $stmt->execute();

if (!$ok) {
  http_response_code(500);
  echo json_encode(['error' => $stmt->error]);
  exit;
}

echo json_encode(['success' => true]);