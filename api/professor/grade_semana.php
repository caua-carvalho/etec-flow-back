<?php
// grade_semana.php

// força retorno JSON e mostra erros em dev
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

// pega parâmetros
$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$turma_id     = isset($_GET['turma_id'])     ? (int) $_GET['turma_id']     : 0;

if (!$professor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

// monta SQL com joins na nova modelagem

$sql = "
SELECT
    g.id_grade,
    dv.id_divisao,
    t.id_turma,
    t.codigo         AS turma,
    dv.nome_divisao  AS divisao,
    e.nome           AS escola,
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    ds.nome          AS disciplina,
    g.sala           AS sala,
    p.nome           AS professor,
    g.cor_evento
FROM grade_aulas g
JOIN divisoes    dv ON dv.id_divisao   = g.id_divisao
JOIN turmas      t  ON t.id_turma       = dv.id_turma
JOIN cursos      c  ON c.id_curso       = t.id_curso
JOIN escolas     e  ON e.id_escola      = c.id_escola
JOIN disciplinas ds ON ds.id_disciplina = g.id_disciplina
JOIN professores p  ON p.id_professor   = g.id_professor
WHERE p.id_professor = ?
  AND t.id_turma     = ?
ORDER BY t.codigo, dv.nome_divisao, g.dia_semana, g.horario_inicio
";

// filtro opcional por turma
if ($turma_id) {
    $sql .= " AND t.id_turma = ?";
}
// ordenação para facilitar agrupamento no front
$sql .= " ORDER BY e.nome, t.codigo, dv.nome_divisao, g.dia_semana, g.horario_inicio";

$stmt = $mysqli->prepare($sql);
if ($turma_id) {
    $stmt->bind_param('ii', $professor_id, $turma_id);
} else {
    $stmt->bind_param('i', $professor_id);
}

$stmt->execute();

// pega resultado
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

// devolve JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);