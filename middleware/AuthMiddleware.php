<?php
require_once __DIR__ . '/../src/jwt_utils.php';

function validarToken($rolRequerido = 'ADMIN') {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token no proporcionado"]);
        exit;
    }

    list($tipo, $token) = explode(" ", $headers['Authorization']);
    $decoded = verificarToken($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token inválido o expirado"]);
        exit;
    }

    if ($rolRequerido && $decoded->rol !== $rolRequerido) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "No tiene permisos"]);
        exit;
    }

    return $decoded;
}