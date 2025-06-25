<?php
// api/cordenador/disciplinas/escolas_cursos.php
header('Content-Type: application/json; charset=utf-8');

// 1) Validação do parâmetro
if (!isset($_GET['coordenador_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro coordenador_id é obrigatório']);
    exit;
}
$coordenador_id = intval($_GET['coordenador_id']);

// 2) Conexão ao banco (ajuste com suas credenciais ou include)
$host = 'sql113.infinityfree.com';
$user = 'if0_39241532';
$pass = 'sua_senha';
$db   = 'if0_39241532_etec_flow';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha na conexão: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset('utf8mb4');

// 3) Query: traz escolas e cursos associados ao coordenador
$sql = <<<SQL
SELECT
  e.id_escola,
  e.nome         AS nome_escola,
  c.id_curso,
  c.nome         AS nome_curso
FROM escolas e
INNER JOIN cursos c
  ON c.id_escola = e.id_escola
INNER JOIN coordenador_cursos cc
  ON cc.id_curso = c.id_curso
WHERE cc.id_coordenador = ?
ORDER BY e.nome, c.nome;
SQL;

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $coordenador_id);
$stmt->execute();
$result = $stmt->get_result();

// 4) Monta agrupamento por escola
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id_escola'];
    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'id_escola'   => $id,
            'nome_escola' => $row['nome_escola'],
            'cursos'      => []
        ];
    }
    $grouped[$id]['cursos'][] = [
        'id_curso' => $row['id_curso'],
        'nome'     => $row['nome_curso']
    ];
}
$stmt->close();
$conn->close();

// 5) Retorna array reindexado
echo json_encode(array_values($grouped), JSON_UNESCAPED_UNICODE);
