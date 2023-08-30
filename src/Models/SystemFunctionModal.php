<?php
namespace Src\Models;

class SystemFunctionModal
{
    private $db = null;
    public function __construct($db)
    {
        $this->db = $db;
    }

    // select function by name function handler
    public function findAllFucntionsByNameDbHandler($function_name)
    {
        $selectQuery = "SELECT * FROM `system_function` WHERE `function_name` = ?";
        try {
            $statement = $this->db->prepare($selectQuery);
            $statement->execute(array($function_name));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }
    }

    // select all function handler
    public function findAllFucntionsDbHandler()
    {
        $selectQuery = "SELECT * FROM `system_function`";
        try {
            $statement = $this->db->prepare($selectQuery);
            $statement->execute(array());
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }
    }

    // insert new FunctionName
    public function insertNewFunctionDbHandler($data, $user_id)
    {
        $InsertQuery = "INSERT INTO system_function (function_name, function_description,createdB_by) VALUES (:function_name, :function_description,:createdB_by)";
        try {
            $statement = $this->db->prepare($InsertQuery);
            $statement->execute(array(
                ':function_name' => $data['function_name'],
                ':function_description' => $data['function_description'],
                ':createdB_by' => $user_id,
            ));
            return $statement;
        } catch (\PDOException$e) {
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
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }

    }

    // select role by id
    public function findRoleByIdDbHandler($role_id)
    {
        $selectQuery = "SELECT * FROM `roles` WHERE `role_id` = ?";
        try {
            $statement = $this->db->prepare($selectQuery);
            $statement->execute(array($role_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }
    }

}
