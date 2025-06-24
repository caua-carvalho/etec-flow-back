<?php
// api/cordenador/professores.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$res = $mysqli->query("SELECT id_professor AS id, nome FROM professores ORDER BY nome");
$prof = [];
while ($row = $res->fetch_assoc()) {
  $prof[] = $row;
}

echo json_encode(['professores'=>$prof], JSON_UNESCAPED_UNICODE);
