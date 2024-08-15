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
        $statement = "INSERT INTO `invintation_tamplete_letter`(`id`, `title`, `body`, cohort_id, `letter_type`, `created_by`) VALUES (:id, :title, :body, :cohort_id, :letter_type, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':cohort_id' => $data['cohort_id'],
                ':letter_type' => $data['letter_type'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get invitation letter by cohort id
     * @param {STRING} {cohortId}
     * @return {OBJECT} {results}
     */
    public function selectInvintationLetterByCohort($cohortId)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE cohort_id = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohortId));
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
     * get ONE invitation letter by cohort id and type
     * @param {STRING, STRING} $cohortId, $letter_type
     * @return {OBJECT} $results
     */
    public function selectInvintationLetterByCohortIdAndType($cohort_id, $letter_type)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE cohort_id = :cohort_id AND letter_type = :letter_type  LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":cohort_id" => $cohort_id,
                ":letter_type" => $letter_type,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get invitation letter by cohort id and type
     * @param {STRING, STRING} {cohort_id, letter_type}
     * @return {OBJECT} {results}
     */
    public function selectInvintationLetterByType($cohort_id, $letter_type)
    {
        $statement = "SELECT * FROM `invintation_tamplete_letter` WHERE cohort_id = :cohort_id AND letter_type = :letter_type LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":cohort_id" => $cohort_id,
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
        $statement = "UPDATE `invintation_tamplete_letter` SET `title`=:title,`body`=:body, `cohort_id`=:cohort_id, `letter_type`=:letter_type,`updated_by`=:updated_by,`status`=:status WHERE `id`=:id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":title" => $data['title'],
                ":body" => $data['body'],
                ":cohort_id" => $data['cohort_id'],
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
