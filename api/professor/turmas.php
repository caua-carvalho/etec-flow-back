<?php
// grade_turma.php
require_once __DIR__ . '/../../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$id_turma     = isset($_GET['id_turma'])      ? (int) $_GET['id_turma']      : 0;
if (!$professor_id || !$id_turma) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetros: professor_id ou id_turma'], JSON_UNESCAPED_UNICODE));
}

$sql = "
SELECT
    g.id_grade,
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    ds.nome          AS disciplina,
    dv.nome_divisao  AS divisao,
    t.codigo         AS turma,
    g.sala           AS sala,
    p.nome           AS professor,
    g.cor_evento
FROM grade_aulas g
JOIN divisoes   dv ON dv.id_divisao   = g.id_divisao
JOIN turmas     t  ON t.id_turma       = dv.id_turma
JOIN disciplinas ds ON ds.id_disciplina = g.id_disciplina
JOIN professores p  ON p.id_professor   = g.id_professor
WHERE g.id_professor = ?
  AND dv.id_turma     = ?
ORDER BY g.dia_semana, g.horario_inicio
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $professor_id, $id_turma);
$stmt->execute();

$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);