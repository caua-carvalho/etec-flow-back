<?php
// api/coordinator/disciplinas_create.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$data = json_decode(file_get_contents('php://input'), true);
$coordenador_id = (int)($data['coordenador_id'] ?? 0);
$curso_id       = (int)($data['curso_id']       ?? 0);
$nome           = trim($data['nome']            ?? '');
$abreviacao     = trim($data['abreviacao']      ?? '');
$cor            = trim($data['cor_evento']       ?? '');

if (!$coordenador_id || !$curso_id || !$nome) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica vínculo coordenador-curso
$chk = $mysqli->prepare(
  "SELECT 1 FROM coordenador_cursos WHERE id_coordenador=? AND id_curso=?"
);
$chk->bind_param('ii', $coordenador_id, $curso_id);
$chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Insere disciplina
$ins = $mysqli->prepare(
  "INSERT INTO disciplinas (id_curso,nome,abreviacao,cor_evento)
   VALUES (?,?,?,?)"
);
$ins->bind_param('isss', $curso_id, $nome, $abreviacao, $cor);
if (!$ins->execute()) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
  'success'       => true,
  'id_disciplina' => $mysqli->insert_id
], JSON_UNESCAPED_UNICODE);
