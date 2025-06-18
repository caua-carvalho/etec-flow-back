<?php
// grade_grid.php

// força retorno JSON e mostra erros em dev
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conn.php';

// pega o professor_id da query string
$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

// horários válidos, na mesma ordem do seu front
$horarios = [
    '07:30','08:20','09:10','10:00','10:20',
    '11:10','12:00','13:00','13:50','14:40','15:30'
];

// Query: traz dia_semana (1=Segunda…5=Sex), horário, turma, professor, disciplina, sala e cor
$sql = "
SELECT
  g.dia_semana,
  g.horario_inicio,
  t.codigo    AS turma,
  p.nome      AS titulo,
  d.nome      AS aula,
  g.sala      AS sala,
  g.cor_evento
FROM grade_aulas g
JOIN turmas     t ON t.id_turma      = g.id_turma
JOIN cursos     c ON c.id_curso      = t.id_curso
JOIN escolas    e ON e.id_escola     = c.id_escola
JOIN disciplinas d ON d.id_disciplina = g.id_disciplina
JOIN professores p ON p.id_professor = g.id_professor
WHERE g.id_professor = ?
  AND g.dia_semana BETWEEN 1 AND 5
ORDER BY g.dia_semana, g.horario_inicio
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

// monta a resposta no formato que o front consome
$response = [];
foreach ($rows as $r) {
    // converte “HH:MM:SS” em “HH:MM”
    $time = substr($r['horario_inicio'], 0, 5);
    // índice na lista de horários
    $horaIndex = array_search($time, $horarios, true);
    if ($horaIndex === false) {
        // pula horários não mapeados
        continue;
    }
    $response[] = [
        'dia'   => $r['dia_semana'] - 1,  // 1=Segunda->[0], …, 5=Sex->[4]
        'hora'  => $horaIndex,
        'turma' => $r['turma'],
        'titulo'=> $r['titulo'],
        'aula'  => $r['aula'],
        'sala'  => $r['sala'],
        'cor'   => $r['cor_evento'],
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
