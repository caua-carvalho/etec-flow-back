<?php
// api/cordenador/grade_delete.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$id_grade = (int)($_POST['id_grade']??0);
if (!$id_grade) {
    http_response_code(400);
    exit(json_encode(['error'=>'Parâmetro id_grade obrigatório'], JSON_UNESCAPED_UNICODE));
}

$sql = "DELETE FROM grade_aulas WHERE id_grade=?";
$stmt= $mysqli->prepare($sql);
$stmt->bind_param('i',$id_grade);
if (!$stmt->execute()) {
    http_response_code(500);
    exit(json_encode(['error'=>$stmt->error], JSON_UNESCAPED_UNICODE));
}

echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
