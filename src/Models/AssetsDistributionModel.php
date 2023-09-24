<?php
namespace Src\Models;

use Error;

class AssetsDistributionModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new distribution batch asset
     * @param OBJECT $data
     * @param STRING $created_by
     * @return VOID
     */
    public function insertNewDistributionBatch($data, $created_by)
    {
        $statement = "INSERT INTO `assets_distriution_batch`(`id`, `title`, `assets_categories_id`, `assets_number_limit`, `created_by`) VALUES (:id, :title,:assets_categories_id, :assets_number_limit, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':title' => $data['title'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get one distribution assets batch by category
     * @param STRING $id
     * @return OBJECT $results
     */
    public function selectDistributionBatchByCategory($batchId, $assetsCategoriesId)
    {
        $statement = "SELECT B.*, AC.assets_categories_name FROM `assets_distriution_batch` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id
        WHERE B.assets_categories_id = :assets_categories_id AND B.id = :id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                "assets_categories_id" => $assetsCategoriesId,
                ":id" => $batchId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get one distribution assets batch
     * @param STRING $id
     * @return OBJECT $results
     */
    public function selectDistributionBatchById($id)
    {
        $statement = "SELECT B.*, AC.assets_categories_name FROM `assets_distriution_batch` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id
        WHERE B.id = ? LIMIT 1";
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
     * get All distribution assets batch
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllDistributionBatch()
    {
        $statement = "SELECT B.*, AC.assets_categories_name FROM `assets_distriution_batch` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id WHERE B.`status` = ?";
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
     * update distribution assets batch
     * @param OBJECT $data
     * @param NUMBER $id
     * @return NUMBER $results
     */
    public function updateDistributionBatch($data, $id, $logged_user_id)
    {
        $statement = "UPDATE `assets_distriution_batch` SET `title`=:title,`assets_categories_id`=:assets_categories_id,`assets_number_limit`=:assets_number_limit,`updated_by`=:updated_by,`status`=:status
        WHERE `id` = :id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':title' => $data['title'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':status' => $data['status'],
                ':updated_by' => $logged_user_id,
                ':id' => $id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * selecting all school that has batch and category
     * @param OBJECT $data
     * @return OBJECT $results
     */
    public function selectSchoolDistributionByCategory($data)
    {
        $statement = "SELECT * FROM `assets_distriution_school` WHERE `batch_id`= :batch_id and `assets_categories_id`= :assets_categories_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":batch_id" => $data['batch_id'],
                ":assets_categories_id" => $data['assets_categories_id'],
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * checking if school already have distributed to this bacth
     * @param OBJECT $data
     * @return OBJECT $results
     */
    public function selectDistributionSchool($data)
    {
        $statement = "SELECT * FROM `assets_distriution_school` WHERE `batch_id`= :batch_id and `level_code`= :level_code and `school_code`= :school_code and `assets_categories_id`= :assets_categories_id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":batch_id" => $data['batch_id'],
                ":level_code" => $data['level_code'],
                ":school_code" => $data['school_code'],
                ":assets_categories_id" => $data['assets_categories_id'],
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update Insert new school Distribution
     * @param OBJECT $data
     * @param NUMBER $id
     * @return NUMBER $results
     */
    public function insertNewSchoolDistribution($data, $logged_user_id)
    {
        $statement = "INSERT INTO `assets_distriution_school`(`id`, `batch_id`, `level_code`, `school_code`, `assets_categories_id`, `assets_sub_categories_id`, `brand_id`, `specification`, `assets_number_limit`, `created_by`, `brand_name`, `assets_categories_name`, `assets_sub_categories_name`, `level_name`, `school_name`) VALUES (:id, :batch_id, :level_code, :school_code, :assets_categories_id, :assets_sub_categories_id, :brand_id, :specification, :assets_number_limit,:created_by, :brand_name, :assets_categories_name, :assets_sub_categories_name,:level_name,:school_name)";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':batch_id' => $data['batch_id'],
                ':level_code' => $data['level_code'],
                ':school_code' => $data['school_code'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => isset($data['assets_sub_categories_id']) ? $data['assets_sub_categories_id'] : null,
                ':brand_id' => $data['brand_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':specification' => json_encode($data['specification']),
                ':brand_name' => $data['brand_name'],
                ':assets_categories_name' => $data['assets_categories_name'],
                ':assets_sub_categories_name' => isset($data['assets_sub_categories_name']) ? $data['assets_sub_categories_name'] : null,
                ':level_name' => $data['level_name'],
                ':school_name' => $data['school_name'],
                ':created_by' => $logged_user_id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get level by level_code
     * @param Number $levelCode
     * @return OBJECT $results
     */
    public function selectLevelsByLevelCode($levelCode)
    {
        $statement = "SELECT * FROM `levels` WHERE `level_code` = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($levelCode));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get schools by school_code
     * @param NUMBER $schoolCode
     * @return OBJECT $results
     */
    public function selectSchoolBySchoolCode($schoolCode)
    {
        $statement = "SELECT * FROM `schools` WHERE `school_code` = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($schoolCode));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
