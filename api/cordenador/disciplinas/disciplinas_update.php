<?php
// api/coordinator/disciplinas_update.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$data = json_decode(file_get_contents('php://input'), true);
$id             = (int)($data['id_disciplina'] ?? 0);
$coordenador_id = (int)($data['coordenador_id'] ?? 0);
$nome           = trim($data['nome']            ?? '');
$abreviacao     = trim($data['abreviacao']      ?? '');
$cor            = trim($data['cor_evento']       ?? '');

if (!$id || !$coordenador_id || !$nome) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica disciplina pertence a curso do coordenador
$sql = "
 SELECT 1
   FROM disciplinas d
   JOIN coordenador_cursos cc ON cc.id_curso = d.id_curso
  WHERE d.id_disciplina = ?
    AND cc.id_coordenador = ?
";
$chk = $mysqli->prepare($sql);
$chk->bind_param('ii', $id, $coordenador_id);
$chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Atualiza
$upd = $mysqli->prepare(
  "UPDATE disciplinas
      SET nome=?, abreviacao=?, cor_evento=?
    WHERE id_disciplina=?"
);
$upd->bind_param('sssi', $nome, $abreviacao, $cor, $id);
if (!$upd->execute()) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
