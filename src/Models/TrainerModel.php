<?php
namespace Src\Models;

use Error;

class TrainerModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Insert New trainer
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function insertNewTrainer($data, $created_by)
    {
        $statement = "INSERT INTO `trainers`(`trainers_id`, `user_id`, `training_id`, `created_by`) VALUES (:trainers_id,:user_id,:training_id,:created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainers_id' => $data['trainers_id'],
                ':user_id' => $data['user_id'],
                ':training_id' => $data['training_id'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * check if trainer has training
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function checkIfTrainerAssignedtoThisTraining($user_id, $training_id)
    {
        $statement = "SELECT * FROM `trainers` WHERE `user_id`=:user_id AND `training_id`=:training_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':training_id' => $training_id,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get trainer details
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function getTrainerDatails($user_id)
    {
        $statement = "SELECT U.user_id, U.phone_numbers, U.email, U.nid, U.sex, U.full_name, U.first_name, U.middle_name, U.last_name, TN.training_id, T.trainingName, T.trainingDescription, T.startDate, T.endDate  FROM `trainers` TN
        INNER JOIN users U ON U.user_id = TN.user_id
        INNER JOIN trainings T ON T.trainingId = TN.training_id
        WHERE TN.`user_id`=:user_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':user_id' => $user_id));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get trainer details by trainer_id
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function getTrainerById($trainer_id)
    {
        $statement = "SELECT U.user_id, U.phone_numbers, U.email, U.nid, U.sex, U.full_name, U.first_name, U.middle_name, U.last_name, TN.training_id, T.trainingName, T.trainingDescription, T.startDate, T.endDate  FROM `trainers` TN
        INNER JOIN users U ON U.user_id = TN.user_id
        INNER JOIN trainings T ON T.trainingId = TN.training_id
        WHERE TN.`trainers_id`=:trainers_id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':trainers_id' => $trainer_id));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get all trainers details by training id
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function getAllTrainersByTrainingId($training_id)
    {
        $statement = "SELECT U.user_id, TN.trainers_id, U.phone_numbers, U.email, U.nid, U.sex, U.full_name, U.first_name, U.middle_name, U.last_name, TN.training_id, T.trainingName, T.trainingDescription, T.startDate, T.endDate  FROM `trainers` TN
        INNER JOIN users U ON U.user_id = TN.user_id
        INNER JOIN trainings T ON T.trainingId = TN.training_id
        WHERE TN.`training_id`=:training_id AND TN.status = 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':training_id' => $training_id));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get all trainers details by training id
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function getAllTrainers()
    {
        $statement = "SELECT U.user_id, U.phone_numbers, U.email, U.nid, U.sex, U.full_name, U.first_name, U.middle_name, U.last_name, TN.training_id, T.trainingName, T.trainingDescription, T.startDate, T.endDate  FROM `trainers` TN
        INNER JOIN users U ON U.user_id = TN.user_id
        INNER JOIN trainings T ON T.trainingId = TN.training_id";
        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update trainer
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function updateTrainer($data, $trainers_id, $logged_user_id)
    {
        $statement = "UPDATE `trainers` SET `training_id`=:training_id,`status`=:status,`updated_by`=:updated_by WHERE `trainers_id`=:trainers_id";
        try {
            //``='[value-1]'
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':training_id' => $data['training_id'],
                ':status' => $data['status'],
                ':updated_by' => $logged_user_id,
                ':trainers_id' => $trainers_id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
