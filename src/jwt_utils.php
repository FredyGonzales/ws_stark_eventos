<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

const JWT_SECRET_KEY = 'St4rkEv3nt0s2026@'; // cámbiala por una propia

function generarToken($usuario, $rol) {
    $payload = [
        'usuario' => $usuario,
        'rol' => $rol,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 hora de validez
    ];

    return JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
}

function verificarToken($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}