<?php
// api/cordenador/disciplinas_create.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_coordenador = intval($input['cordenador_id'] ?? 0);
$id_curso       = intval($input['id_curso']      ?? 0);
$nome           = trim($mysqli->real_escape_string($input['nome'] ?? ''));
$abreviacao     = trim($mysqli->real_escape_string($input['abreviacao'] ?? ''));
$cor_evento     = trim($mysqli->real_escape_string($input['cor_evento'] ?? '#CCCCCC'));

if (!$id_coordenador || !$id_curso || !$nome) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados incompletos']);
  exit;
}

// validar vínculo
$stmtChk = $mysqli->prepare(
  "SELECT 1 FROM coordenador_cursos WHERE id_coordenador = ? AND id_curso = ?"
);
$stmtChk->bind_param('ii', $id_coordenador, $id_curso);
$stmtChk->execute();
if (!$stmtChk->get_result()->fetch_assoc()) {
  http_response_code(403);
  echo json_encode(['error' => 'Não autorizado']);
  exit;
}

// inserir
$stmt = $mysqli->prepare(
  "INSERT INTO disciplinas (id_curso, nome, abreviacao, cor_evento)
   VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('isss', $id_curso, $nome, $abreviacao, $cor_evento);
$ok = $stmt->execute();

if (!$ok) {
  http_response_code(500);
  echo json_encode(['error' => $stmt->error]);
  exit;
}
$newId = $stmt->insert_id;

echo json_encode([ 'id' => $newId, 'nome' => $nome, 'abreviacao' => $abreviacao, 'cor_evento' => $cor_evento ]);