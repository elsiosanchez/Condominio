<?php
require_once __DIR__ . '/../inc/headers.php';
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../middleware/auth.php'; // Para la función generate_jwt

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email y contraseña son requeridos.']);
    exit;
}

$email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
$password = $data->password;

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de email inválido.']);
    exit;
}

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Contraseña correcta, generar JWT
        $payload = [
            'iat' => time(), // Issued at: time when the token was generated
            'exp' => time() + (60 * 60 * 24), // Expiration time (24 hours)
            'user_id' => $user['id'],
            'user_name' => $user['name']
        ];
        
        $token = generate_jwt($payload, JWT_SECRET);

        // No devolver el hash de la contraseña
        unset($user['password']);

        echo json_encode([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en el servidor: ' . $e->getMessage()]);
}
