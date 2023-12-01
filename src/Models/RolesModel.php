<?php
namespace Src\Models;

use Error;

class RolesModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function findAll()
    {
        $statement = "SELECT * FROM roles WHERE status = ? ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    // assign access to role
    public function assignAccessToRoleDbHandler($acess, $role_id)
    {
        $updateQuery = "UPDATE `roles` SET `access` = ? WHERE `role_id` = ?";

        try {
            $statement = $this->db->prepare($updateQuery);
            $statement->execute(array($acess, $role_id));
            return $statement;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

    }

    public function findById($role_id)
    {
        $statement = "SELECT * FROM roles WHERE role_id = ? AND status = ? ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($role_id, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findRoleByName($role)
    {
        $statement = "SELECT `role_id` FROM `roles` WHERE `role` = ? AND `status` = ?";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($role, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
