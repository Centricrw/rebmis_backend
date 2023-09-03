<?php
namespace Src\Models;

use Error;

class TrainingTypeModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new training type
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function insertNewTrainingType($data, $created_by)
    {
        $statement = "INSERT INTO `training_type`(`training_type_id`, `training_type_name`, `description`, `created_by`) VALUES (:training_type_id,:training_type_name, :description, :created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $training_type_name = trim($data['training_type_name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':training_type_id' => $data['training_type_id'],
                ':training_type_name' => strtolower($training_type_name),
                ':description' => $data['description'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get training type by name
     * @param {String} training_type_name
     * @return {OBJECT} {results}
     */
    public function getTrainingTypeByName($training_type_name)
    {
        $statement = "SELECT * FROM `training_type` WHERE `training_type_name` = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($training_type_name));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE training type
     * @param {String} training_center_id
     * @return {OBJECT} {results}
     */
    public function selectTrainingTypeById($training_center_id)
    {
        $statement = "SELECT * FROM `training_type` WHERE training_type_id = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($training_center_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Assign training type to training
     * @param {String}
     * @return {OBJECT} {results}
     */
    public function assignTrainingTypeToTraining($data, $created_by)
    {
        $statement = "UPDATE `trainings` SET `training_type_id`=:training_type_id, `updatedBy`=:updatedBy WHERE `trainingId`=:trainingId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":training_type_id" => $data['training_type_id'],
                ":trainingId" => $data['training_id'],
                ":updatedBy" => $created_by,
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Get assigned training types to training
     * @param {String}
     * @return {OBJECT} {results}
     */
    public function selectTrainingsAssignedToTrainingType($training_type_id)
    {
        $statement = "SELECT * FROM `trainings` WHERE `training_type_id` = :training_type_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":training_type_id" => $training_type_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All training type
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function selectAllTrainingsType()
    {
        $statement = "SELECT * FROM `training_type`";
        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
