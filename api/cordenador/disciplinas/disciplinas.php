<?php
// api/coordinator/disciplinas.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$coordenador_id = isset($_GET['coordenador_id']) ? (int)$_GET['coordenador_id'] : 0;
$curso_id       = isset($_GET['curso_id'])       ? (int)$_GET['curso_id']       : 0;
if (!$coordenador_id || !$curso_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: coordenador_id ou curso_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lista somente disciplinas de cursos vinculados ao coordenador
$sql = "
  SELECT d.id_disciplina, d.nome, d.abreviacao, d.cor_evento
    FROM disciplinas d
    JOIN coordenador_cursos cc
      ON cc.id_curso = d.id_curso
   WHERE cc.id_coordenador = ?
     AND d.id_curso        = ?
   ORDER BY d.nome
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $coordenador_id, $curso_id);
$stmt->execute();
$res = $stmt->get_result();
$lista = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode(['disciplinas' => $lista], JSON_UNESCAPED_UNICODE);
