<?php
// schedule.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$turma_id     = isset($_GET['turma_id'])     ? (int) $_GET['turma_id']     : 0;

if (!$professor_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE));
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
JOIN cursos      c      ON c.id_curso       = t.id_curso
JOIN escolas     e      ON e.id_escola      = c.id_escola
JOIN disciplinas ds     ON ds.id_disciplina = g.id_disciplina
JOIN professores p      ON p.id_professor   = g.id_professor
WHERE p.id_professor = ?";

if ($turma_id) {
    $sql .= " AND t.id_turma = ?";
}
$sql .= "\nORDER BY ha.periodo, ha.indice, dv.nome_divisao";

$stmt = $mysqli->prepare($sql);
if ($turma_id) {
    $stmt->bind_param('ii', $professor_id, $turma_id);
} else {
    $stmt->bind_param('i', $professor_id);
}
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);