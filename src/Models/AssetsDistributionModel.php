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
        $statement = "INSERT INTO `assets_distriution_batch`(`id`, `title`, `created_by`) VALUES (:id, :title, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':title' => $data['title'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new distribution batch details
     * @param OBJECT $data
     * @return VOID
     */
    public function insertNewBatchDetails($data)
    {
        $statement = "INSERT INTO `batch_details`(`id`, `batch_id`, `assets_categories_id`, `assets_number_limit`, `assets_sub_categories_id`, `brand_id`, `specification`) VALUES (:id, :batch_id, :assets_categories_id, :assets_number_limit, :assets_sub_categories_id, :brand_id, :specification)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':batch_id' => $data['batch_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':assets_sub_categories_id' => $data['assets_sub_categories_id'],
                ':brand_id' => $data['brand_id'],
                ':specification' => json_encode($data['specification']),
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update distribution batch details
     * @param OBJECT $data
     * @return VOID
     */
    public function updateBatchDetails($data, $batchId)
    {
        $statement = "UPDATE `batch_details` SET `batch_id` = :batch_id, `assets_categories_id` = :assets_categories_id, `assets_number_limit` = :assets_number_limit, `assets_sub_categories_id` = :assets_sub_categories_id, `brand_id` = :brand_id, `specification` = :specification WHERE `id` = :id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $batchId,
                ':batch_id' => $data['batch_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':assets_sub_categories_id' => $data['assets_sub_categories_id'],
                ':brand_id' => $data['brand_id'],
                ':specification' => json_encode($data['specification']),
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get batch details by batch_id
     * @param OBJECT $batch
     * @return OBJECT $results
     */
    function getBatchDetails($batch)
    {
        $convertSpecification = function ($value) {
            $value['specification'] = json_decode($value['specification']);
            return $value;
        };
        $batchDetails = "SELECT B.*, AC.assets_categories_name, ADB.title, ASUB.name as assets_sub_categories_name, Br.name as brand_name  FROM `batch_details` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id
        INNER JOIN `assets_distriution_batch` ADB ON ADB.id = B.batch_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = B.assets_sub_categories_id
        INNER JOIN `Brands` Br ON Br.id = B.brand_id
        WHERE B.batch_id = :batch_id";
        try {
            $statement = $this->db->prepare($batchDetails);
            $statement->execute(array(":batch_id" => $batch['id']));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $resultsDetails = array_map($convertSpecification, $results);
            $batch['batch_details'] = $resultsDetails;
            return $batch;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select batch details by batch_id and category_id
     * @param STRING $batch_id
     * @param STRING $category_id
     * @return OBJECT $results
     */
    public function selectBatchDetailsByBatchIDAndCategory($batch_id, $category_id)
    {
        $statement = "SELECT * FROM `batch_details` WHERE `batch_id` = :batch_id AND `assets_categories_id` = :assets_categories_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':batch_id' => $batch_id,
                ':assets_categories_id' => $category_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset limits for batch definition
     * @param OBJECT $data
     * @return ARRAY $results
     */
    public function selectDistributionBatchByCategory($data)
    {
        $statement = "SELECT B.*, AC.assets_categories_name, ADB.title, ASUB.name as assets_sub_categories_name, Br.name as brand_name  FROM `batch_details` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id
        INNER JOIN `assets_distriution_batch` ADB ON ADB.id = B.batch_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = B.assets_sub_categories_id
        INNER JOIN `Brands` Br ON Br.id = B.brand_id
        WHERE B.assets_categories_id = :assets_categories_id AND B.assets_sub_categories_id = :assets_sub_categories_id AND B.brand_id = :brand_id AND B.batch_id = :batch_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":assets_categories_id" => $data['assets_categories_id'],
                ":assets_sub_categories_id" => $data['assets_sub_categories_id'],
                ":brand_id" => $data['brand_id'],
                ":batch_id" => $data['batch_id'],
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
        $statement = "SELECT *FROM `assets_distriution_batch` WHERE id = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $batchDetails = array_map(array($this, 'getBatchDetails'), $results);
            return $batchDetails;
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
        $statement = "SELECT * FROM `assets_distriution_batch` WHERE `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $batchDetails = array_map(array($this, 'getBatchDetails'), $results);
            return $batchDetails;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All distribution assets batch
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllDistributionBatchStatus($status = 'approved')
    {
        $statement = "SELECT * FROM `assets_distriution_batch` WHERE `batch_status` = ? AND `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($status, 1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $batchDetails = array_map(array($this, 'getBatchDetails'), $results);
            return $batchDetails;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All distribution assets batch
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllDistributionBatchBYusedId($logged_user_id)
    {
        $statement = "SELECT * FROM `assets_distriution_batch` WHERE `created_by` = ?  AND  `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($logged_user_id, 1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $batchDetails = array_map(array($this, 'getBatchDetails'), $results);
            return $batchDetails;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All distribution assets batch
     * @param STRING $batchDefinitionId
     * @return OBJECT $results
     */
    public function selectAllSchoolDistributionDetails($batchDefinitionId)
    {
        $statement = "SELECT A.*, L.level_name, S.school_name FROM `assets_distriution_school` A
        INNER JOIN `levels` L ON L.level_code = A.level_code
        INNER JOIN `schools` S ON S.school_code = A.school_code
        WHERE A.`batch_details_id` = :batchDefinitionId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array("batchDefinitionId" => $batchDefinitionId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * count assets distributed on school
     * @param STRING $batchDefinitionId
     * @return OBJECT $results
     */
    public function selectCountAssetsDistributedOnSchool($schoolCode, $batchDefinitionId)
    {
        $statement = "SELECT COUNT(*) as total FROM `assets` WHERE `school_code` = :school_code AND `batch_details_id`=:batch_details_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":school_code" => $schoolCode,
                ":batch_details_id" => $batchDefinitionId,
            ));
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
        $statement = "UPDATE `assets_distriution_batch` SET `title` = :title, `batch_status` = :batch_status,`updated_by` = :updated_by, `status` = :status
        WHERE `id` = :id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':title' => $data['title'],
                ':batch_status' => $data['batch_status'],
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
     * selecting batch definition
     * @param STRING $id
     * @return OBJECT $results
     */
    public function selectBatchDefinitionBYId($id)
    {
        $statement = "SELECT * FROM `batch_details` WHERE `id`=:id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":id" => $id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * selecting all school that has batch and category
     * @param STRING $ids
     * @return ARRAY $results
     */
    public function selectSchoolDistributionByCategory($ids)
    {
        $statement = "SELECT * FROM `assets_distriution_school` WHERE `batch_details_id` IN ('$ids')";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * selecting stock details
     * @param STRING $ids
     * @return ARRAY $results
     */
    public function selectAssetsDetails($data)
    {
        $statement = "SELECT * FROM `assets` WHERE assets_categories_id = :assets_categories_id AND assets_sub_categories_id = :assets_sub_categories_id AND brand_id = :brand_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":assets_categories_id" => $data['assets_categories_id'],
                ":assets_sub_categories_id" => $data['assets_sub_categories_id'],
                ":brand_id" => $data['brand_id'],
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
        $statement = "SELECT * FROM `assets_distriution_school` WHERE `batch_id`= :batch_id and `level_code`= :level_code and `school_code`= :school_code and `batch_details_id`= :batch_details_id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":batch_id" => $data['batch_id'],
                ":level_code" => $data['level_code'],
                ":school_code" => $data['school_code'],
                ":batch_details_id" => $data['batch_details_id'],
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
        $statement = "INSERT INTO `assets_distriution_school`(`assets_school_distribution_id`, `batch_id`, `level_code`, `school_code`, `batch_details_id`,`assets_number_limit`, `created_by`) VALUES (:id, :batch_id, :level_code, :school_code, :batch_details_id, :assets_number_limit, :created_by)";
        try {
            // Remove white spaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['assets_school_distribution_id'],
                ':batch_id' => $data['batch_id'],
                ':level_code' => $data['level_code'],
                ':school_code' => $data['school_code'],
                ':batch_details_id' => $data['batch_details_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':created_by' => $logged_user_id,
            ));
            return $statement->rowCount();
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
    public function updateSchoolDistribution($data, $logged_user_id)
    {
        $statement = "UPDATE `assets_distriution_school` SET `batch_id` = :batch_id, `level_code` = :level_code, `school_code` = :school_code, `batch_details_id` = :batch_details_id,`assets_number_limit` = :assets_number_limit, `updated_by` = :updated_by WHERE `assets_school_distribution_id`=:assets_school_distribution_id";
        try {
            // Remove white spaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_school_distribution_id' => $data['assets_school_distribution_id'],
                ':batch_id' => $data['batch_id'],
                ':level_code' => $data['level_code'],
                ':school_code' => $data['school_code'],
                ':batch_details_id' => $data['batch_details_id'],
                ':assets_number_limit' => $data['assets_number_limit'],
                ':updated_by' => $logged_user_id,
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

    /**
     * selecting batch definition
     * @param STRING $id
     * @return OBJECT $results
     */
    public function selectBatchDefinitionCategoryAndSubCategoryBrands($data)
    {
        $statement = "SELECT * FROM `batch_details` WHERE `brand_id`=:brand_id AND `assets_sub_categories_id`=:assets_sub_categories_id AND `assets_categories_id`=:assets_categories_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":assets_categories_id" => $data['assets_categories_id'],
                ":assets_sub_categories_id" => $data['assets_sub_categories_id'],
                ":brand_id" => $data['brand_id'],
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * selecting stock details
     * @param STRING $ids
     * @return ARRAY $results
     */
    public function selectCountAssignedAssets($batchDetailsId)
    {
        $statement = "SELECT COUNT(*) as total FROM `assets` WHERE `batch_details_id` = :batch_details_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":batch_details_id" => $batchDetailsId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select batch details by batch_id and category_id
     * @param STRING $batch_id
     * @param STRING $category_id
     * @return OBJECT $results
     */
    public function selectBatchDetailsByBatchID($batch_id)
    {
        $statement = "SELECT * FROM `batch_details` WHERE `batch_id` = :batch_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':batch_id' => $batch_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select assets on batch
     * @param STRING $batch_details_id
     * @return OBJECT $results
     */
    public function selectBatchAssetsDetailsByBatchID($batch_details_id)
    {
        $statement = "SELECT A.*, L.level_name, S.school_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name, Br.name as brand_name  FROM `assets` A
        INNER JOIN `schools` S ON S.school_code = A.school_code
        INNER JOIN `levels` L ON L.level_code = A.level_code
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = A.assets_categories_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = A.assets_sub_categories_id
        INNER JOIN `Brands` Br ON Br.id = A.brand_id
        WHERE A.batch_details_id = :batch_details_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':batch_details_id' => $batch_details_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                $results[$key]['specification'] = json_decode($value['specification']);
            }
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select assets on batch
     * @param STRING $batch_details_id
     * @return OBJECT $results
     */
    public function selectBatchAssetsDetailsByBatchIDEngravedUser($batch_details_id, $logged_user_id)
    {
        $statement = "SELECT A.*, L.level_name, S.school_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name, Br.name as brand_name  FROM `assets` A
        INNER JOIN `schools` S ON S.school_code = A.school_code
        INNER JOIN `levels` L ON L.level_code = A.level_code
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = A.assets_categories_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = A.assets_sub_categories_id
        INNER JOIN `Brands` Br ON Br.id = A.brand_id
        WHERE A.batch_details_id = :batch_details_id AND A.updated_by = :updated_by";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':batch_details_id' => $batch_details_id,
                ':updated_by' => $logged_user_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                $results[$key]['specification'] = json_decode($value['specification']);
            }
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
