<?php
namespace Src\Models;

use Error;

class InvitationLetterModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create New Invitation tamplete
     * @param {OBJECT, STRING} {data, created_by}
     * @return {VOID}
     */
    public function insertNewInvitationTammplete($data, $created_by)
    {
        $statement = "INSERT INTO `invintation_tamplete_letter`(`id`, `title`, `body`, `trainingId`, `letter_type`, `created_by`) VALUES (:id, :title, :body, :trainingId, :letter_type, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':trainingId' => $data['trainingId'],
                ':letter_type' => $data['letter_type'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get invitation letter by training id
     * @param {STRING} {trainingId}
     * @return {OBJECT} {results}
     */
    public function selectInvintationLetterByTraining($trainingId)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE trainingId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($trainingId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE invitation letter by id
     * @param {STRING} {id}
     * @return {OBJECT} {results}
     */
    public function selectInvintationLetterById($id)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE id = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE invitation letter by training id and type
     * @param {STRING, STRING} $trainingId, $letter_type
     * @return {OBJECT} $results
     */
    public function selectInvintationLetterByTrainingIdAndType($trainingId, $letter_type)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE trainingId = :trainingId AND letter_type = :letter_type  LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $trainingId,
                ":letter_type" => $letter_type,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get invitation letter by training id and type
     * @param {STRING, STRING} {trainingId, letter_type}
     * @return {OBJECT} {results}
     */
    public function selectInvintationLetterByType($trainingId, $letter_type)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE trainingId = :trainingId AND letter_type = :letter_type LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $trainingId,
                ":letter_type" => $letter_type,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All invitation letter
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function selectAllInvintationLetter()
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update invitation letter
     * @param {OBJECT, STRING, STRING} {data, updated_by}
     * @return {OBJECT} {results}
     */
    public function updateInvintationLetter($data, $updated_by)
    {
        $statement = "UPDATE `invintation_tamplete_letter` SET `title`=:title,`body`=:body, `trainingId`=:trainingId, `letter_type`=:letter_type,`updated_by`=:updated_by,`status`=:status WHERE `id`=:id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":title" => $data['title'],
                ":body" => $data['body'],
                ":trainingId" => $data['trainingId'],
                ":letter_type" => $data['letter_type'],
                ":updated_by" => $updated_by,
                ":status" => $data['status'],
                ":id" => $data['id'],
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
