<?php
require_once __DIR__ . '/../config/Conexion.php';

class EmpresaModel extends Conexion
{
    public function obtenerEmpresa()
    {
        $sql = "SELECT * FROM stark_empresa LIMIT 1";
        $stmt = $this->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEmpresa($id, $data)
    {
        $sql = "UPDATE stark_empresa 
                SET ruc_empresa = :ruc_empresa,
                    gls_empresa = :gls_empresa,
                    direccion = :direccion,
                    telefono = :telefono,
                    correo = :correo,
                    aud_usr_modificacion = :aud_usr_modificacion,
                    aud_fec_modificacion = NOW()
                WHERE id_empresa = :id";
        $stmt = $this->prepare($sql);
        $stmt->bindParam(':ruc_empresa', $data['ruc_empresa']);
        $stmt->bindParam(':gls_empresa', $data['gls_empresa']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':correo', $data['correo']);
        $stmt->bindParam(':aud_usr_modificacion', $data['aud_usr_modificacion']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>