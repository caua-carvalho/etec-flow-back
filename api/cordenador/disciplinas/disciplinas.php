<?php
// api/cordenador/cursos.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$id_coordenador = intval($_GET['cordenador_id'] ?? 0);
$id_escola      = intval($_GET['id_escola'] ?? 0);

if (!$id_coordenador || !$id_escola) {
  http_response_code(400);
  echo json_encode(['error' => 'Parâmetros missing']);
  exit;
}

// só cursos do coordenador e da escola
$sql = "
  SELECT c.id_curso AS id, c.nome
  FROM cursos c
  JOIN coordenador_cursos cc ON cc.id_curso = c.id_curso
  WHERE cc.id_coordenador = ? AND c.id_escola = ?
  ORDER BY c.nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $id_coordenador, $id_escola);
$stmt->execute();
$res = $stmt->get_result();

$cursos = [];
while ($row = $res->fetch_assoc()) {
  $cursos[] = $row;
}

echo json_encode(['cursos' => $cursos], JSON_UNESCAPED_UNICODE);