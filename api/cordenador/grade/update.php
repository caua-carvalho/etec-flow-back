<?php
// api/cordenador/grade_update.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/conn.php';

$id_grade     = (int)($_POST['id_grade']     ?? 0);
$id_disciplina= (int)($_POST['id_disciplina']?? 0);
$sala         = trim($_POST['sala']         ?? '');
$cor_evento   = trim($_POST['cor_evento']   ?? '');

if (!$id_grade||!$id_disciplina) {
    http_response_code(400);
    exit(json_encode(['error'=>'Parâmetros obrigatórios faltando'], JSON_UNESCAPED_UNICODE));
}

$sql = "
  UPDATE grade_aulas
     SET id_disciplina=?, sala=?, cor_evento=?
   WHERE id_grade=?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('issi',$id_disciplina,$sala,$cor_evento,$id_grade);
if (!$stmt->execute()) {
    http_response_code(500);
    exit(json_encode(['error'=>$stmt->error], JSON_UNESCAPED_UNICODE));
}

echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
