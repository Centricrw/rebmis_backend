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
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':training_centers_id' => $data['training_centers_id'],
                ':training_centers_name' => $data['training_centers_name'],
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
}
