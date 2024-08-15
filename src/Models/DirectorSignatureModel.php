<?php
namespace Src\Models;

use Error;

class DirectorSignatureModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insertDirectorSignature($data)
    {
        $statement = "INSERT INTO `directorSignature`(`Signator_id`, `user_id`, `director_name`, `director_role`, `director_institution`, `director_signature_url`, `training_id`, `position`) VALUES (:Signator_id, :user_id, :director_name, :director_role, :director_institution, :director_signature_url, :training_id, :position)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":Signator_id" => $data['Signator_id'],
                ":user_id" => $data['user_id'],
                ":director_name" => $data['director_name'],
                ":director_role" => $data['director_role'],
                ":director_institution" => $data['director_institution'],
                ":director_signature_url" => $data['director_signature_url'],
                ":training_id" => $data['training_id'],
                ":position" => $data['position'],
            ));
            $result = $statement->rowCount();
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectDirectorSignatureBYTraining($training_id)
    {
        $statement = "SELECT * FROM `directorSignature` WHERE `training_id`=:training_id AND `status`=:status ORDER BY `position` ASC";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":training_id" => $training_id, ":status" => 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectDirectorSignatureUserTraining($training_id, $user_id)
    {
        $statement = "SELECT * FROM `directorSignature` WHERE `training_id`=:training_id AND `user_id`=:user_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":training_id" => $training_id,
                ":user_id" => $user_id,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectDirectorSignatureBYUser($user_id)
    {
        $statement = "SELECT * FROM `directorSignature` WHERE `user_id`=:user_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":user_id" => $user_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectDirectorSignatureBYId($Signator_id)
    {
        $statement = "SELECT * FROM `directorSignature` WHERE `Signator_id`=:Signator_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":Signator_id" => $Signator_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateDirectorSignatureInfo($Signator_id, $data)
    {
        $statement = "UPDATE `directorSignature` SET `director_name`=:director_name,`director_role`=:director_role, `director_institution`=:director_institution, `training_id`=:training_id, `position`=:position,`status`=:status WHERE `Signator_id`=:Signator_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":Signator_id" => $Signator_id,
                ":director_name" => $data['director_name'],
                ":director_role" => $data['director_role'],
                ":director_institution" => $data['director_institution'],
                ":training_id" => $data['training_id'],
                ":position" => $data['position'],
                ":status" => $data['status'],
            ));
            $result = $statement->rowCount();
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateSignature($Signator_id, $director_signature_url)
    {
        $statement = "UPDATE `directorSignature` SET `director_signature_url`=:director_signature_url WHERE `Signator_id`=:Signator_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":Signator_id" => $Signator_id,
                ":director_signature_url" => $director_signature_url,
            ));
            $result = $statement->rowCount();
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
