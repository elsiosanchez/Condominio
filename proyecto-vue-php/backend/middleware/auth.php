<?php
// NOTA IMPORTANTE DE SEGURIDAD:
// Esta es una implementación de JWT muy básica y SÓLO para fines de demostración.
// NO LA USES EN PRODUCCIÓN. En un proyecto real, instala una librería robusta como
// `firebase/php-jwt` vía Composer: `composer require firebase/php-jwt`.

function base64UrlEncode($text) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
}

function generate_jwt($payload, $secret) {
    $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
    $headers_encoded = base64UrlEncode(json_encode($headers));
    $payload_encoded = base64UrlEncode(json_encode($payload));
    $signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64UrlEncode($signature);
    return "$headers_encoded.$payload_encoded.$signature_encoded";
}

function validate_jwt($jwt, $secret) {
    list($headers_encoded, $payload_encoded, $signature_encoded) = explode('.', $jwt);
    $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $signature_encoded));
    $expected_signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secret, true);
    
    if (!hash_equals($expected_signature, $signature)) {
        return null; // Firma inválida
    }

    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload_encoded)), true);
    
    // Verificar si el token ha expirado
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null; // Token expirado
    }

    return $payload;
}

function get_bearer_token() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function require_auth() {
    $token = get_bearer_token();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Acceso no autorizado: Token no proporcionado.']);
        exit;
    }

    $payload = validate_jwt($token, JWT_SECRET);
    if (!$payload) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso denegado: Token inválido o expirado.']);
        exit;
    }
    
    // Retorna el payload para que el script que lo llama pueda usarlo (ej. user_id)
    return $payload;
}
