<?php
// grade_turma.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$turma_id     = isset($_GET['turma_id'])     ? (int) $_GET['turma_id']     : 0;

if (!$professor_id || !$turma_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetros: professor_id ou turma_id'], JSON_UNESCAPED_UNICODE));
}

$sql = "
SELECT
    g.id_grade,
    g.dia_semana,   
    t.id_turma,
    t.codigo           AS turma,
    dv.id_divisao,
    dv.nome_divisao    AS divisao,
    ha.periodo         AS periodo,
    ha.indice          AS indice,
    ha.horario_inicio,
    ha.horario_fim,
    ds.nome            AS disciplina,
    p.nome             AS professor,
    g.sala             AS sala,
    g.cor_evento
FROM grade_aulas g
JOIN horarios_aula ha   ON ha.id_horario   = g.id_horario
JOIN divisoes    dv     ON dv.id_divisao   = g.id_divisao
JOIN turmas      t      ON t.id_turma       = dv.id_turma
JOIN disciplinas ds     ON ds.id_disciplina = g.id_disciplina
JOIN professores p      ON p.id_professor   = g.id_professor
WHERE p.id_professor = ?
  AND t.id_turma     = ?
ORDER BY ha.periodo, ha.indice, dv.nome_divisao
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $professor_id, $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);


// turmas.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE));
}

$sql = "
SELECT DISTINCT
    t.id_turma,
    t.codigo,
    dv.nome_divisao AS divisao,
    c.nome         AS curso
FROM turmas t
JOIN cursos    c  ON c.id_curso    = t.id_curso
JOIN divisoes  dv ON dv.id_turma    = t.id_turma
JOIN grade_aulas g  ON g.id_divisao = dv.id_divisao
JOIN professores p  ON p.id_professor = g.id_professor
WHERE p.id_professor = ?
ORDER BY c.nome, t.codigo, dv.nome_divisao
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);