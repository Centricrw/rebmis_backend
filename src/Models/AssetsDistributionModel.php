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
    public function selectDistributionBatchByCategory($assetsCategoriesId)
    {
        $statement = "SELECT B.*, AC.assets_categories_name FROM `assets_distriution_batch` B
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = B.assets_categories_id
        WHERE B.assets_categories_id = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assetsCategoriesId));
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
}
