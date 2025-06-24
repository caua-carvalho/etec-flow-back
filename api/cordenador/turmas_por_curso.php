<?php
// api/cordenador/turmas_por_curso.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

$curso_id = isset($_GET['curso_id']) ? (int) $_GET['curso_id'] : 0;
if (!$curso_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: curso_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT 
        t.id_turma    AS id,
        t.codigo      AS codigo
      FROM turmas t
     WHERE t.id_curso = ?
     ORDER BY t.codigo
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $curso_id);
$stmt->execute();
$result = $stmt->get_result();

$turmas = [];
while ($row = $result->fetch_assoc()) {
    $turmas[] = [
        'id'     => (int)$row['id'],
        'codigo' => $row['codigo'],
    ];
}

echo json_encode(['turmas' => $turmas], JSON_UNESCAPED_UNICODE);
