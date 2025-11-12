<?php
require_once __DIR__ . '/../model/UsuarioModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['correo']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $correo = trim($data['correo']);
        $password = trim($data['password']);

        $usuario = $this->usuarioModel->validarLogin($correo, $password);

        if ($usuario && !isset($usuario['error'])) {
            $key = "St4rkEv3nt0s2026@";
            $payload = [
                'id' => $usuario['id'],
                'nombre_completo' => $usuario['nombre_completo'],
                'correo' => $usuario['gls_correo'],
                'rol' => $usuario['tipo_usuario'],
                'iat' => time(),
                'exp' => time() + (60 * 60) // 1 hora
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            echo json_encode([
                'success' => true,
                'usuario' => $usuario,
                'token' => $jwt
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas o no tiene rol ADMIN']);
        }
    }
}
?>