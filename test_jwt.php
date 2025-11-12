<?php
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "clave_secreta_demo";
$payload = [
    "usuario" => "fredy",
    "rol" => "ADMIN",
    "iat" => time(),
    "exp" => time() + 3600
];

$jwt = JWT::encode($payload, $key, 'HS256');
echo "TOKEN: " . $jwt . "\n";

$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
print_r($decoded);
?>