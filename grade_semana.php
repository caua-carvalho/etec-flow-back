<?php
// grade_semana.php

// Força retorno JSON e evita qualquer saída HTML
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);

require_once __DIR__ . 'config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
SELECT
    g.id_grade,
    g.id_turma,
    t.codigo       AS turma,
    g.dia_semana,
    g.horario_inicio,
    g.horario_fim,
    d.nome         AS disciplina,
    g.sala         AS sala,
    g.cor_evento
FROM grade_aulas g
JOIN turmas      t ON t.id_turma     = g.id_turma
JOIN disciplinas d ON d.id_disciplina = g.id_disciplina
WHERE g.id_professor = ?
ORDER BY g.dia_semana, g.horario_inicio
";

if (! $stmt = $mysqli->prepare($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha na preparação da query'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('i', $professor_id);
$stmt->execute();

// Se mysqlnd não estiver disponível, substitua get_result() por bind/fetch
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
