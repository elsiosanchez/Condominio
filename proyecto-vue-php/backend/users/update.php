<?php
require_once __DIR__ . '/../inc/headers.php';
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../middleware/auth.php';

require_auth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de usuario inválido.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nombre y email son requeridos.']);
    exit;
}

$name = htmlspecialchars(strip_tags($data->name));
$email = filter_var($data->email, FILTER_VALIDATE_EMAIL);

try {
    $pdo = connectDB();
    
    if (!empty($data->password)) {
        // Si se proporciona una nueva contraseña, hashearla y actualizarla
        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
        $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'id' => $id]);
    } else {
        // Si no, actualizar solo nombre y email
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
    }

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $updatedUser = $stmt->fetch();
        echo json_encode(['success' => true, 'data' => $updatedUser]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado o sin cambios.']);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23505 || $e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'El email ya está en uso por otro usuario.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
    }
}
