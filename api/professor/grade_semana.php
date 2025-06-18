<?php
// grade_semana.php

// força retorno JSON e mostra erros em dev
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conn.php';

// pega parâmetros
$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$turma_id     = isset($_GET['turma_id'])     ? (int) $_GET['turma_id']     : 0;

if (!$professor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

// monta SQL com joins até escolas
$sql = "
SELECT
    g.id_grade,
    g.id_turma,
    t.codigo      AS turma,
    e.nome        AS escola,      -- nome da escola
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    d.nome        AS disciplina,
    g.sala        AS sala,
    g.cor_evento
FROM grade_aulas g
JOIN turmas   t ON t.id_turma   = g.id_turma
JOIN cursos   c ON c.id_curso   = t.id_curso
JOIN escolas  e ON e.id_escola  = c.id_escola
JOIN disciplinas d ON d.id_disciplina = g.id_disciplina
WHERE g.id_professor = ?
";

// filtra por turma, se vier
if ($turma_id) {
    $sql .= " AND g.id_turma = ? ";
}

$sql .= " ORDER BY g.dia_semana, g.horario_inicio";

$stmt = $mysqli->prepare($sql);
if (! $stmt) {
    http_response_code(500);
    // expõe o erro real do MySQL em dev
    echo json_encode([
      'error' => 'Falha na preparação da query',
      'mysql_error' => $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// bind com 1 ou 2 parâmetros
if ($turma_id) {
    $stmt->bind_param('ii', $professor_id, $turma_id);
} else {
    $stmt->bind_param('i', $professor_id);
}

$stmt->execute();

// pega resultado
// se não tiver mysqlnd, troque por bind/fetch
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

// devolve JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
