<?php
// Permite ejecutar el script desde cualquier lugar
require_once __DIR__ . '/../config/db_config.php';

try {
    $pdo = connectDB();
    echo "Conexión a la base de datos exitosa.\n";

    $email = 'admin@test.com';
    $name = 'Admin User';
    $password = 'admin123';

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        echo "El usuario '{$email}' ya existe. No se realizaron cambios.\n";
        exit;
    }

    // Hashear la contraseña
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insertar el usuario
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword
    ]);

    echo "Usuario '{$email}' creado exitosamente con la contraseña '{$password}'.\n";

} catch (PDOException $e) {
    die("Error al sembrar la base de datos: " . $e->getMessage() . "\n");
}

