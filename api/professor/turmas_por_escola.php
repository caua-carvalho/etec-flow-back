<?php
// turmas_por_escola.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/conn.php';

$professor_id = isset($_GET['professor_id']) ? (int) $_GET['professor_id'] : 0;
if (!$professor_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Faltando parâmetro: professor_id'], JSON_UNESCAPED_UNICODE));
}

$sql = "
    SELECT
    e.id_escola,
    e.nome      AS nome_escola,
    t.id_turma,
    t.codigo    AS codigo_turma,
    dv.nome_divisao
    FROM grade_aulas ga
    JOIN divisoes dv    ON ga.id_divisao    = dv.id_divisao
    JOIN turmas t       ON dv.id_turma      = t.id_turma
    JOIN cursos c       ON t.id_curso       = c.id_curso
    JOIN escolas e      ON c.id_escola      = e.id_escola
    WHERE ga.id_professor = ?
    GROUP BY e.id_escola, t.id_turma
    ORDER BY e.nome, t.codigo;
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $professor_id);
$stmt->execute();
$result = $stmt->get_result();
// agrupa turmas por escola
$escolas = [];
foreach ($rows as $r) {
    $idEscola = $r['id_escola'];
    if (!isset($escolas[$idEscola])) {
        $escolas[$idEscola] = [
            'nome'   => $r['nome_escola'],
            'turmas' => []
        ];
    }
    // monta o código da turma (você pode remover a divisão se não quiser)
    $codigo = $r['codigo_turma'] . $r['nome_divisao'];

    $escolas[$idEscola]['turmas'][] = [
        'id'     => (int) $r['id_turma'],
        'codigo' => $codigo
    ];
}

// 5) Retorna o JSON agrupado
echo json_encode(array_values($escolas), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);