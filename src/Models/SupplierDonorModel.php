<?php
namespace Src\Models;

use Error;

class SupplierDonorModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function selectAll()
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE status = ? ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
    public function selectById($id)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE id=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectByName($name)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE name=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($name));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectByType($type)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE type=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($type));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function insertNewSupplier($data, $logged_user_id)
    {
        $statement = "INSERT INTO `supplierDonor_tbl`(`id`, `name`, `institution`, `description`, `created_by`, `type`) VALUES (:id, :name, :institution,:description,:created_by,:type)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':name' => strtolower(trim($data['name'])),
                ':institution' => isset($data['institution']) ? $data['institution'] : null,
                ':description' => isset($data['description']) ? $data['description'] : null,
                ':created_by' => $logged_user_id,
                ':type' => $data['type'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateSupplier($data)
    {
        $statement = "UPDATE `supplierDonor_tbl` SET `name`=:name,`institution`=:institution,`description`=:description,`status`=:status,`type`=:type WHERE `id`=:id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':name' => strtolower(trim($data['name'])),
                ':institution' => isset($data['institution']) ? $data['institution'] : null,
                ':description' => isset($data['description']) ? $data['description'] : null,
                ':status' => $data['status'],
                ':type' => $data['type'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
