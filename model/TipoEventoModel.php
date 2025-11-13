<?php
require_once __DIR__ . '/../config/Conexion.php';

class TipoEventoModel {
    private $conn;

    public function __construct() {
        // Usamos directamente la clase Conexion que extiende PDO
        $this->conn = new Conexion();
    }

    // ==========================================
    // LISTAR
    // ==========================================
    public function listar($page = 1, $limit = 5) {
        $offset = ($page - 1) * $limit;

        try {
            $sql = "SELECT * FROM stark_tipo_evento 
                    ORDER BY id_tipo_evento DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Total de registros
            $totalStmt = $this->conn->query("SELECT COUNT(*) as total FROM stark_tipo_evento");
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'eventos' => $eventos,
                'page' => $page,
                'total_pages' => ceil($total / $limit),
                'total' => $total
            ];

        } catch (PDOException $e) {
            return ['error' => 'Error al listar: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // CREAR
    // ==========================================
    public function crear($data) {
        try {
            $sql = "INSERT INTO stark_tipo_evento 
                    (codigo_evento, nombre_evento, descripcion_evento, estado, aud_usr_registro, aud_fec_registro)
                    VALUES (:codigo_evento, :nombre_evento, :descripcion_evento, :estado, :aud_usr_registro, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':codigo_evento', $data['codigo_evento']);
            $stmt->bindValue(':nombre_evento', $data['nombre_evento']);
            $stmt->bindValue(':descripcion_evento', $data['descripcion_evento']);
            $stmt->bindValue(':estado', $data['estado']);
            $stmt->bindValue(':aud_usr_registro', $data['aud_usr_registro']);
            $stmt->execute();

            return ['success' => true, 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Error al crear: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // ACTUALIZAR
    // ==========================================
    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE stark_tipo_evento 
                    SET codigo_evento = :codigo_evento,
                        nombre_evento = :nombre_evento,
                        descripcion_evento = :descripcion_evento,
                        estado = :estado,
                        aud_usr_modificacion = :aud_usr_modificacion,
                        aud_fec_modificacion = NOW()
                    WHERE id_tipo_evento = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_evento', $data['codigo_evento']);
            $stmt->bindValue(':nombre_evento', $data['nombre_evento']);
            $stmt->bindValue(':descripcion_evento', $data['descripcion_evento']);
            $stmt->bindValue(':estado', $data['estado']);
            $stmt->bindValue(':aud_usr_modificacion', $data['aud_usr_modificacion']);
            $stmt->execute();

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // ELIMINAR
    // ==========================================
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM stark_tipo_evento WHERE id_tipo_evento = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }
}
?>