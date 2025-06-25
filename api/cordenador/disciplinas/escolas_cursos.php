<?php
// api/coordinator/courses_grouped.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/conn.php';

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
      e.nome       AS nome_escola,
      c.id_curso,
      c.nome       AS nome_curso
    FROM escolas e
    JOIN cursos c
      ON c.id_escola = e.id_escola
    JOIN coordenador_cursos cc
      ON cc.id_curso = c.id_curso
     WHERE cc.id_coordenador = ?
  ORDER BY e.nome, c.nome
";

if (! $stmt = $mysqli->prepare($sql)) {
    http_response_code(500);
    echo json_encode(
        ['error' => 'Erro no banco de dados: ' . $mysqli->error],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$stmt->bind_param('i', $coordenador_id);
$stmt->execute();
$result = $stmt->get_result();
$rows   = $result->fetch_all(MYSQLI_ASSOC);

$grouped = [];
foreach ($rows as $row) {
    $id = (int)$row['id_escola'];
    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'id_escola'   => $id,
            'nome_escola' => $row['nome_escola'],
            'cursos'      => []
        ];
    }
    $grouped[$id]['cursos'][] = [
        'id_curso' => (int)$row['id_curso'],
        'nome'     => $row['nome_curso']
    ];
}

// devolve um array indexado numericamente
echo json_encode(array_values($grouped), JSON_UNESCAPED_UNICODE);
