<?php
// turmas_por_escola.php
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
SELECT
    e.nome          AS escola,
    t.id_turma      AS id,
    t.codigo        AS codigo,
    dv.nome_divisao AS divisao
FROM grade_aulas g
JOIN divisoes   dv ON dv.id_divisao   = g.id_divisao
JOIN turmas     t  ON t.id_turma       = dv.id_turma
JOIN cursos     c  ON c.id_curso       = t.id_curso
JOIN escolas    e  ON e.id_escola      = c.id_escola
JOIN professores p  ON p.id_professor   = g.id_professor
WHERE p.id_professor = ?
GROUP BY e.id_escola, t.id_turma, dv.id_divisao
ORDER BY e.nome, t.codigo, dv.nome_divisao
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $professor_id);
$stmt->execute();
$result = $stmt->get_result();
// agrupa turmas por escola
$map = [];
while ($r = $result->fetch_assoc()) {
    $map[$r['escola']]['turmas'][] = [
        'id'      => $r['id'],
        'codigo'  => $r['codigo'],
        'divisao' => $r['divisao']
    ];
}

$response = [];
foreach ($map as $escola => $data) {
    $response[] = [
        'nome'   => $escola,
        'turmas' => $data['turmas']
    ];
}
echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
