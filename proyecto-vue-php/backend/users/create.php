<?php
require_once __DIR__ . '/../inc/headers.php';
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../middleware/auth.php';

require_auth();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nombre, email y contraseña son requeridos.']);
    exit;
}

$name = htmlspecialchars(strip_tags($data->name));
$email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
$password = $data->password;

if (!$email || empty($name) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword]);

    $newUserId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $newUserId]);
    $newUser = $stmt->fetch();

    http_response_code(201);
    echo json_encode(['success' => true, 'data' => $newUser]);
} catch (PDOException $e) {
    if ($e->getCode() == 23505 || $e->getCode() == 23000) { // Unique violation
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'El email ya está en uso.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al crear el usuario: ' . $e->getMessage()]);
    }
}
