<?php
require_once __DIR__ . '/../config/Conexion.php';

class EstadisticasModel extends Conexion {
    public function obtenerTotales() {
        try {
            $sqlUsuarios = "SELECT COUNT(*) AS total FROM stark_usuario";
            $stmtUsuarios = $this->prepare($sqlUsuarios);
            $stmtUsuarios->execute();
            $usuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC)['total'];

            $sqlClientes = "SELECT COUNT(*) AS total FROM stark_usuario WHERE tipo_usuario = 'CLIENTE'";
            $stmtClientes = $this->prepare($sqlClientes);
            $stmtClientes->execute();
            $clientes = $stmtClientes->fetch(PDO::FETCH_ASSOC)['total'];

            $sqlProveedores = "SELECT COUNT(*) AS total FROM stark_usuario WHERE tipo_usuario = 'PROVEEDOR'";
            $stmtProveedores = $this->prepare($sqlProveedores);
            $stmtProveedores->execute();
            $proveedores = $stmtProveedores->fetch(PDO::FETCH_ASSOC)['total'];

            $sqlSolicitudes = "SELECT COUNT(*) AS total FROM stark_eventos"; // si existe esta tabla
            $stmtSolicitudes = $this->prepare($sqlSolicitudes);
            $stmtSolicitudes->execute();
            $solicitudes = $stmtSolicitudes->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                "usuarios" => (int)$usuarios,
                "clientes" => (int)$clientes,
                "proveedores" => (int)$proveedores,
                "solicitudes" => (int)$solicitudes
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>