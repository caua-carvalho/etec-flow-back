<?php
require_once __DIR__ . '/../config/conn.php';

$professor_id = isset($_GET['professor_id']) 
    ? (int) $_GET['professor_id'] 
    : 0;

if (!$professor_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetro: professor_id']));
}

$sql = "
SELECT
    g.id_grade,
    g.id_turma,
    t.codigo       AS turma,
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    d.nome         AS disciplina,
    g.sala         AS sala,          -- usa o campo direto de grade_aulas
    g.cor_evento
FROM grade_aulas g
JOIN turmas      t ON t.id_turma     = g.id_turma
JOIN disciplinas d ON d.id_disciplina = g.id_disciplina
WHERE g.id_professor = ?
ORDER BY g.dia_semana, g.horario_inicio
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $professor_id);
$stmt->execute();

// Garanta mysqlnd ou substitua get_result(), se for o caso
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

// Resposta JSON com unicode não escapado
echo json_encode($data, JSON_UNESCAPED_UNICODE);
