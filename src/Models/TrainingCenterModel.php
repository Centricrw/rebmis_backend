<?php
namespace Src\Models;

use Error;

class TrainingCenterModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new training center
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function insertNewTrainingCenter($data, $created_by)
    {
        $statement = "INSERT INTO `training_centers`(`training_centers_id`, `training_centers_name`, `district_code`, `district_name`, `createdBy`) VALUES (:training_centers_id,:training_centers_name,:district_code,:district_name,:createdBy)";
        try {
            // Remove whitespaces from both sides of a string
            $training_center_name = trim($data['training_centers_name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':training_centers_id' => $data['training_centers_id'],
                ':training_centers_name' => strtolower($training_center_name),
                ':district_code' => $data['district_code'],
                ':district_name' => $data['district_name'],
                ':createdBy' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get training center by name
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function getTrainingCenterByName($training_center_name)
    {
        $statement = "SELECT * FROM `training_centers` WHERE training_centers_name = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($training_center_name));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE training center
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function getTrainingCenterById($training_center_id)
    {
        $statement = "SELECT * FROM `training_centers` WHERE training_centers_id = ? LIMIT 1";
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
     * Assign training center to training
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function assignTrainingCanterToTraining($training_center_id, $training_id, $created_by)
    {
        $statement = "INSERT INTO `trainings_to_training_center`( `trainingId`, `training_centers_id`, `createdBy`) VALUES (:trainingId,:training_centers_id,:createdBy)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $training_id,
                ":training_centers_id" => $training_center_id,
                ":createdBy" => $created_by,
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Get assigned training center and training
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function assignedTrainingCenterAndTraining($training_center_id, $training_id)
    {
        $statement = "SELECT * FROM `trainings_to_training_center` WHERE trainingId = :trainingId AND training_centers_id = :training_centers_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $training_id,
                ":training_centers_id" => $training_center_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Delete assigned training center and training
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function deleteAssignedTrainingCenterAndTraining($training_center_id, $training_id)
    {
        $statement = "DELETE FROM `trainings_to_training_center` WHERE trainingId = :trainingId AND training_centers_id = :training_centers_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $training_id,
                ":training_centers_id" => $training_center_id,
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All training center
     * @param {NULL}
     * @return {OBJECT} {results}
     */
    public function getAllTrainingCenter()
    {
        $statement = "SELECT * FROM `training_centers`";
        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * all training centers assigned to training
     * @param {STRING} training_id
     * @return {OBJECT} {results}
     */
    public function GetAllCentersAssignedToTraining($training_id)
    {
        $statement = "SELECT TC.* FROM `training_centers` TC
        INNER JOIN `trainings_to_training_center` TT ON TT.`training_centers_id` = TC.`training_centers_id` WHERE TT.`trainingId` = :trainingId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":trainingId" => $training_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
