<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/conn.php';

// lê JSON do body
$input = json_decode(file_get_contents('php://input'), true);
$username = $mysqli->real_escape_string($input['username']  ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
  http_response_code(400);
  exit(json_encode(['error'=>'Faltam username ou password']));
}

// Query única que busca:
// - dados do usuário (u)
// - role (r.nome)
// - id_coordenador (c.id_coordenador)
// - id_professor    (p.id_professor) via match de email
$sql = "
  SELECT 
    u.id_usuario,
    u.username,
    u.password_hash,
    u.email,
    r.nome           AS role,
    COALESCE(c.id_coordenador, 0) AS coordenadorId,
    COALESCE(p.id_professor, 0)   AS professorId
  FROM usuarios u
  JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
  JOIN roles r          ON ur.id_role   = r.id_role
  LEFT JOIN usuario_coordenador uc 
    ON u.id_usuario = uc.id_usuario
  LEFT JOIN coordenadores c 
    ON uc.id_coordenador = c.id_coordenador
  LEFT JOIN professores p 
    ON u.email = p.email
  WHERE u.username = ?
  LIMIT 1
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$hashInput = hash('sha256', $password);

if ( ! $user || ! hash_equals($user['password_hash'], $hashInput) ) {
  http_response_code(401);
  exit(json_encode(['error'=>'Credenciais inválidas']));
}

// gera token simples (substitua por JWT se preferir)
$token = bin2hex(random_bytes(16));

echo json_encode([
  'token' => $token,
  'user'  => [
    'id'             => (int)$user['id_usuario'],
    'username'       => $user['username'],
    'email'          => $user['email'],
    'role'           => $user['role'],
    'coordenadorId'  => (int)$user['coordenadorId'],  // 0 se não for coordenador
    'professorId'    => (int)$user['professorId']     // 0 se não for professor
  ]
]);
