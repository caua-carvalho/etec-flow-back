<?php
// grade_aulas_turma.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/conn.php';

$turma_id = isset($_GET['turma_id']) ? (int) $_GET['turma_id'] : 0;
if (!$turma_id) {
    http_response_code(400);
    exit(json_encode([
        'error' => 'Faltando parâmetro: turma_id'
    ], JSON_UNESCAPED_UNICODE));
}

$sql = "
SELECT
    g.id_grade,
    g.dia_semana,
    t.id_turma,
    t.codigo            AS turma,
    dv.id_divisao,
    dv.nome_divisao     AS divisao,
    g.posicao_aula      AS indice,
    ht.horario_inicio,
    ht.horario_fim,
    ds.nome             AS disciplina,
    ds.abreviacao       AS disciplina_abreviada,
    p.nome              AS professor,
    g.sala              AS sala,
    g.cor_evento
FROM grade_aulas g
JOIN divisoes    dv ON dv.id_divisao      = g.id_divisao
JOIN turmas      t  ON t.id_turma         = dv.id_turma
JOIN cursos      c  ON c.id_curso         = t.id_curso
JOIN horario_tipo ht 
     ON ht.id_tipo_horario = c.id_tipo_horario
    AND ht.posicao         = g.posicao_aula
JOIN disciplinas ds ON ds.id_disciplina   = g.id_disciplina
JOIN professores p  ON p.id_professor     = g.id_professor
WHERE t.id_turma = ?
ORDER BY ht.posicao, dv.nome_divisao
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
