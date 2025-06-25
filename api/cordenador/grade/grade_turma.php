<?php
// /cordenador/grade/grade_turma.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
if (!$turma_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro turma_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
  1) Subquery "slots" gera todas as combinações de dia 2–6 (Seg→Sex)
     com cada posição de aula do modelo de horário da turma.
  2) LEFT JOIN em grade_aulas para preencher quando existir,
     ou NULL quando não houver.
*/
$sql = "
SELECT
  s.dia_semana,
  s.posicao_aula,
  s.horario_inicio,
  s.horario_fim,
  g.id_grade,
  g.id_divisao,
  g.id_disciplina,
  ds.abreviacao         AS disciplina_abreviada,
  g.sala,
  ds.cor_evento,
  g.id_professor,
  p.nome                AS professor,
  dv.nome_divisao       AS divisao
FROM (
  SELECT
    t.id_turma,
    d.dia_semana,
    ht.posicao       AS posicao_aula,
    ht.horario_inicio,
    ht.horario_fim
  FROM turmas t
  /* cruza com dias da semana úteis */
  JOIN (
    SELECT 2 AS dia_semana
    UNION ALL SELECT 3
    UNION ALL SELECT 4
    UNION ALL SELECT 5
    UNION ALL SELECT 6
  ) d
  /* traz o modelo de horário da turma */
  JOIN cursos c
    ON c.id_curso = t.id_curso
  JOIN horario_tipo ht
    ON ht.id_tipo_horario = c.id_tipo_horario
  WHERE t.id_turma = ?
) AS s
/* agora traz as aulas cadastradas (se existirem) */
LEFT JOIN grade_aulas g
  ON g.id_turma      = s.id_turma
 AND g.dia_semana    = s.dia_semana
 AND g.posicao_aula  = s.posicao_aula
/* e seus dados auxiliares */
LEFT JOIN disciplinas   ds ON ds.id_disciplina   = g.id_disciplina
LEFT JOIN professores   p  ON p.id_professor     = g.id_professor
LEFT JOIN divisoes      dv ON dv.id_divisao      = g.id_divisao
ORDER BY s.posicao_aula, s.dia_semana
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$res  = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);

/* devolve JSON com todos os slots, preenchidos ou não */
echo json_encode($data, JSON_UNESCAPED_UNICODE);
