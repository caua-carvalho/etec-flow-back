<?php
// turmas_por_escola.php

header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

// recebe professor_id
$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

// busca escolas + turmas onde o professor leciona
$sql = "
  SELECT
    e.nome   AS escola,
    t.codigo AS turma
  FROM grade_aulas g
  JOIN turmas   t ON t.id_turma   = g.id_turma
  JOIN cursos   c ON c.id_curso   = t.id_curso
  JOIN escolas  e ON e.id_escola  = c.id_escola
  WHERE g.id_professor = ?
  GROUP BY e.id_escola, t.id_turma
  ORDER BY e.nome, t.codigo
";

$stmt = $mysqli->prepare($sql);
if (! $stmt) {
    http_response_code(500);
    echo json_encode([
      'error'       => 'Erro na preparação da query',
      'mysql_error' => $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('i', $professor_id);
$stmt->execute();

$result = $stmt->get_result();
$rows   = $result->fetch_all(MYSQLI_ASSOC);

// agrupa por escola
$map = [];
foreach ($rows as $r) {
    $esc = $r['escola'];
    if (!isset($map[$esc])) {
        $map[$esc] = [];
    }
    $map[$esc][] = $r['turma'];
}

// monta resposta no formato desejado
$response = [];
foreach ($map as $escola => $turmas) {
    $response[] = [
        'nome'   => $escola,
        'turmas' => array_values($turmas),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
