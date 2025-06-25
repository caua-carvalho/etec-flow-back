<?php
// api/cordenador/disciplinas_delete.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../config/conn.php';

$id             = isset($_GET['id_disciplina'])   ? (int)$_GET['id_disciplina']   : 0;
$coordenador_id = isset($_GET['coordenador_id'])  ? (int)$_GET['coordenador_id']  : 0;
if (!$id || !$coordenador_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros faltando'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Inicia transação
$mysqli->begin_transaction();

try {
    // 1) Apaga todas as aulas dessa disciplina
    $stmt = $mysqli->prepare("
        DELETE ga
          FROM grade_aulas ga
          JOIN disciplinas d ON d.id_disciplina = ga.id_disciplina
          JOIN coordenador_cursos cc ON cc.id_curso = d.id_curso
         WHERE d.id_disciplina = ?
           AND cc.id_coordenador = ?
    ");
    $stmt->bind_param('ii', $id, $coordenador_id);
    $stmt->execute();

    // 2) Apaga a própria disciplina
    $stmt = $mysqli->prepare("
        DELETE d
          FROM disciplinas d
          JOIN coordenador_cursos cc ON cc.id_curso = d.id_curso
         WHERE d.id_disciplina = ?
           AND cc.id_coordenador = ?
    ");
    $stmt->bind_param('ii', $id, $coordenador_id);
    $stmt->execute();

    $mysqli->commit();
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
