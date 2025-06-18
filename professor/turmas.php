<?php
require_once __DIR__ . '/../config/db.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetro: professor_id']));
}

$sql = "
SELECT DISTINCT
    t.id_turma,
    t.codigo,
    t.divisao,
    c.nome AS curso
FROM turmas t
JOIN cursos c ON c.id_curso = t.id_curso
JOIN grade_aulas g ON g.id_turma = t.id_turma
WHERE g.id_professor = ?
ORDER BY c.nome, t.codigo
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);