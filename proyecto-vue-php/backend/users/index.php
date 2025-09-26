<?php
require_once __DIR__ . '/../inc/headers.php';
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../middleware/auth.php';

require_auth(); // Protege el endpoint

try {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $users]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al obtener los usuarios: ' . $e->getMessage()]);
}
