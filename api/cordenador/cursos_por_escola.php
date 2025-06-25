<?php
// api/coordinator/courses_grouped.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

$coordenador_id = isset($_GET['coordenador_id'])
    ? (int) $_GET['coordenador_id']
    : 0;

if (!$coordenador_id) {
    http_response_code(400);
    echo json_encode(
        ['error' => 'Faltando parâmetro: coordenador_id'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$sql = "
    SELECT 
        e.id_escola,
        e.nome      AS nome_escola,
        c.id_curso,
        c.nome      AS nome_curso
      FROM escolas e
 INNER JOIN cursos c  ON c.id_escola       = e.id_escola
 INNER JOIN coordenador_cursos cc 
                      ON cc.id_curso       = c.id_curso
     WHERE cc.id_coordenador = ?
  ORDER BY e.nome, c.nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $coordenador_id);
$stmt->execute();
$result = $stmt->get_result();

$map = [];
while ($row = $result->fetch_assoc()) {
    $escId = (int)$row['id_escola'];
    if (!isset($map[$escId])) {
        $map[$escId] = [
            'id_escola'   => $escId,
            'nome_escola' => $row['nome_escola'],
            'cursos'      => []
        ];
    }
    $map[$escId]['cursos'][] = [
        'id_curso'   => (int)$row['id_curso'],
        'nome'       => $row['nome_curso'],
    ];
}

$escolas = array_values($map);

echo json_encode(
    ['escolas' => $escolas],
    JSON_UNESCAPED_UNICODE
);
