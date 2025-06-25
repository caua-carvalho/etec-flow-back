<?php
// grade_aulas_turma.php api do professor
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
    ht.posicao         AS indice,
    ht.horario_inicio,
    ht.horario_fim,
    t.id_turma,
    t.codigo           AS turma,
    dv.id_divisao,
    dv.nome_divisao    AS divisao,
    g.id_grade,
    g.dia_semana,
    ds.nome             AS disciplina,
    ds.id_disciplina    AS id_disciplina,
    ds.abreviacao       AS disciplina_abreviada,
    p.nome              AS professor,
    g.sala,
    g.cor_evento
FROM turmas t
  JOIN cursos c
    ON c.id_curso = t.id_curso
  JOIN divisoes dv
    ON dv.id_turma = t.id_turma

  -- pega todas as posições do modelo de horário do curso
  JOIN horario_tipo ht
    ON ht.id_tipo_horario = c.id_tipo_horario

  -- só “puxa” grade_aulas quando existir; caso contrário, retorna NULL
  LEFT JOIN grade_aulas g
    ON g.id_divisao   = dv.id_divisao
   AND g.posicao_aula = ht.posicao
   AND g.id_turma     = t.id_turma  -- opcional, se você quiser reforçar o filtro

  -- disciplinas e professores também em LEFT JOIN, para não “queimar” linhas vazias
  LEFT JOIN disciplinas ds
    ON ds.id_disciplina = g.id_disciplina
  LEFT JOIN professores p
    ON p.id_professor   = g.id_professor

WHERE t.id_turma = ?
ORDER BY ht.posicao, dv.nome_divisao;

";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
