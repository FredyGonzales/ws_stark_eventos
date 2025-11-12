<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

class UsuarioModel {
    private $conexion;
    private $aesKey = 'AES'; // Clave usada en AES_ENCRYPT/DECRYPT

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function validarLogin($correo, $password) {
        try {
            $sql = "SELECT id, cod_usuario, nombre_completo, gls_correo, tipo_usuario, estado_usuario
                    FROM stark_usuario
                    WHERE gls_correo = :correo 
                      AND AES_DECRYPT(password, :aesKey) = :password
                      AND tipo_usuario = 'ADMIN' 
                      AND estado_usuario = 'ACTIVO'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':aesKey', $this->aesKey);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            return $usuario ?: false;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function listarUsuarios($page = 1, $limit = 10, $estado = null, $search = null) {
        try {
            $offset = ($page - 1) * $limit;

            // Seleccionamos todos los campos de la tabla
            $sql = "SELECT 
                        id,
                        cod_usuario,
                        tipo_documento,
                        num_documento,
                        nombre_completo,
                        apellido_paterno,
                        apellido_materno,
                        gls_correo,
                        tipo_usuario,
                        aud_usr_registro,
                        aud_fec_registro,
                        aud_usr_modificacion,
                        aud_fec_modificacion,
                        estado_usuario,
                        telefono
                    FROM stark_usuario
                    WHERE 1=1";

            // --- Filtro por estado ---
            if (!empty($estado)) {
                $sql .= " AND estado_usuario = :estado";
            }

            // --- Filtro por búsqueda ---
            if (!empty($search)) {
                $sql .= " AND (nombre_completo LIKE :search OR gls_correo LIKE :search OR cod_usuario LIKE :search)";
            }

            $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conexion->prepare($sql);

            if (!empty($estado)) $stmt->bindValue(':estado', $estado);
            if (!empty($search)) $stmt->bindValue(':search', '%' . $search . '%');
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Contar total de registros
            $countSql = "SELECT COUNT(*) as total FROM stark_usuario WHERE 1=1";
            if (!empty($estado)) $countSql .= " AND estado_usuario = :estado";
            if (!empty($search)) $countSql .= " AND (nombre_completo LIKE :search OR gls_correo LIKE :search OR cod_usuario LIKE :search)";
            $countStmt = $this->conexion->prepare($countSql);
            if (!empty($estado)) $countStmt->bindValue(':estado', $estado);
            if (!empty($search)) $countStmt->bindValue(':search', '%' . $search . '%');
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                "usuarios" => $usuarios,
                "total" => (int)$total,
                "page" => (int)$page,
                "limit" => (int)$limit,
                "total_pages" => ceil($total / $limit)
            ];

        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // UsuarioModel.php

public function actualizarUsuario($id, $datos) {
    try {
        $sql = "UPDATE stark_usuario SET
                    cod_usuario = :cod_usuario,
                    nombre_completo = :nombre_completo,
                    gls_correo = :gls_correo,
                    tipo_usuario = :tipo_usuario,
                    estado_usuario = :estado_usuario,
                    telefono = :telefono,
                    tipo_documento = :tipo_documento,
                    num_documento = :num_documento,
                    aud_usr_modificacion = :aud_usr_modificacion,
                    aud_fec_modificacion = NOW()
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cod_usuario', $datos['cod_usuario']);
        $stmt->bindParam(':nombre_completo', $datos['nombre_completo']);
        $stmt->bindParam(':gls_correo', $datos['gls_correo']);
        $stmt->bindParam(':tipo_usuario', $datos['tipo_usuario']);
        $stmt->bindParam(':estado_usuario', $datos['estado_usuario']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
        $stmt->bindParam(':num_documento', $datos['num_documento']);
        $stmt->bindParam(':aud_usr_modificacion', $datos['aud_usr_modificacion']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return ["success" => true, "message" => "Usuario actualizado correctamente"];
    } catch (PDOException $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// ===============================
// Eliminar usuario
// ===============================
public function eliminarUsuario($id) {
    try {
        $sql = "DELETE FROM stark_usuario WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ["success" => true, "message" => "Usuario eliminado correctamente"];
        } else {
            return ["success" => false, "message" => "Usuario no encontrado"];
        }
    } catch (PDOException $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

public function crearUsuario($datos) {
    try {
        $sql = "INSERT INTO stark_usuario 
                    (cod_usuario, nombre_completo, gls_correo, tipo_usuario, estado_usuario, telefono, password, tipo_documento, num_documento, aud_usr_registro, aud_fec_registro)
                VALUES
                    (:cod_usuario, :nombre_completo, :gls_correo, :tipo_usuario, :estado_usuario, :telefono, AES_ENCRYPT(:password, 'AES'), :tipo_documento, :num_documento, :aud_usr_registro, NOW())";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cod_usuario', $datos['cod_usuario']);
        $stmt->bindParam(':nombre_completo', $datos['nombre_completo']);
        $stmt->bindParam(':gls_correo', $datos['gls_correo']);
        $stmt->bindParam(':tipo_usuario', $datos['tipo_usuario']);
        $stmt->bindParam(':estado_usuario', $datos['estado_usuario']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':password', $datos['password']);
        $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
        $stmt->bindParam(':num_documento', $datos['num_documento']);
        $stmt->bindParam(':aesKey', $this->aesKey);
        $stmt->bindParam(':aud_usr_registro', $datos['aud_usr_registro']); // normalmente es el usuario logueado

        $stmt->execute();

        return ["success" => true, "message" => "Usuario creado correctamente"];
    } catch (PDOException $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

}
?>