<?php
// api/coordinator/schools.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

$coordinator_id = isset($_GET['coordinator_id']) 
    ? (int) $_GET['coordinator_id'] 
    : 0;

if (!$coordinator_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: coordinator_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT DISTINCT 
        e.id_escola, 
        e.nome
      FROM coordenador_cursos cc
      JOIN cursos          c ON c.id_curso   = cc.id_curso
      JOIN escolas         e ON e.id_escola  = c.id_escola
     WHERE cc.id_coordenador = ?
     ORDER BY e.nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $coordinator_id);
$stmt->execute();
$result = $stmt->get_result();

$escolas = [];
while ($row = $result->fetch_assoc()) {
    $escolas[] = [
        'id'   => (int)$row['id_escola'],
        'nome' => $row['nome'],
    ];
}

echo json_encode(['escolas' => $escolas], JSON_UNESCAPED_UNICODE);
