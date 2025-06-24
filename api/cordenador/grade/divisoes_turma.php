<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/conn.php';

$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
if (!$turma_id) {
  http_response_code(400);
  exit(json_encode(['error'=>'Parâmetro turma_id faltando'], JSON_UNESCAPED_UNICODE));
}

$sql = "
  SELECT id_divisao AS id, nome_divisao AS nome
    FROM divisoes
   WHERE id_turma = ?
   ORDER BY nome_divisao
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$res = $stmt->get_result();

$divs = [];
while ($row = $res->fetch_assoc()) {
  $divs[] = $row;
}

echo json_encode(['divisoes'=>$divs], JSON_UNESCAPED_UNICODE);
