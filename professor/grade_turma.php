<?php
require_once __DIR__ . '/../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
$id_turma      = isset($_GET['id_turma'])      ? (int) $_GET['id_turma']      : 0;
if (!$professor_id || !$id_turma) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetros: professor_id ou id_turma']));
}

$sql = "
SELECT
    g.id_grade,
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    d.nome     AS disciplina,
    IFNULL(s.nome, '') AS sala,
    p.nome     AS professor,
    g.cor_evento
FROM grade_aulas g
JOIN disciplinas d ON d.id_disciplina = g.id_disciplina
JOIN professores  p ON p.id_professor   = g.id_professor
LEFT JOIN salas    s ON s.id_sala         = g.id_sala
WHERE g.id_professor = ?
  AND g.id_turma     = ?
ORDER BY g.dia_semana, g.horario_inicio
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $professor_id, $id_turma);
if($stmt->execute() === false){
  http_response_code(400);
}
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);