<?php
// api/cordenador/grade_por_turma.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
if (!$turma_id) {
    http_response_code(400);
    exit(json_encode(['error'=>'Faltando parâmetro turma_id'], JSON_UNESCAPED_UNICODE));
}

$sql = "
  SELECT
    g.id_grade,
    g.dia_semana,
    g.posicao_aula    AS posicao,
    g.id_disciplina,
    ds.abreviacao     AS disciplina_abreviada,
    g.sala,
    g.cor_evento,
    p.id_professor,
    p.nome            AS professor,
    dv.nome_divisao   AS divisao,
    ht.horario_inicio,
    ht.horario_fim
  FROM grade_aulas g
  JOIN disciplinas    ds ON ds.id_disciplina = g.id_disciplina
  JOIN professores    p  ON p.id_professor  = g.id_professor
  JOIN divisoes       dv ON dv.id_divisao   = g.id_divisao
  JOIN cursos         c  ON c.id_curso      = dv.id_turma /* o id_divisao refere-se à turma */
  JOIN horario_tipo   ht 
    ON ht.id_tipo_horario = c.id_tipo_horario
   AND ht.posicao         = g.posicao_aula
  WHERE g.id_turma = ?
  ORDER BY ht.posicao, g.dia_semana
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$rows   = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
