<?php
// Carga las variables de entorno si usas un archivo .env (opcional, requiere paquete)
// O simplemente define las constantes directamente.

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
// Elige 'mysql' o 'pgsql'
define('DB_TYPE', getenv('DB_TYPE') ?: 'mysql'); 

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: (DB_TYPE === 'mysql' ? '3306' : '5432'));
define('DB_NAME', getenv('DB_NAME') ?: 'condominio_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// --- CONFIGURACIÓN DE JWT ---
define('JWT_SECRET', 'mi_secret_jwt_super_segura_12345');

/**
 * Establece una conexión a la base de datos usando PDO.
 * @return PDO
 */
function connectDB() {
    $dbType = DB_TYPE;
    $host = DB_HOST;
    $port = DB_PORT;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;

    try {
        if ($dbType === 'mysql') {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        } elseif ($dbType === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        } else {
            throw new Exception("Tipo de base de datos no soportado: $dbType");
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $user, $pass, $options);
    } catch (Exception $e) {
        // En un entorno de producción, no deberías mostrar detalles del error.
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]);
        exit;
    }
}
