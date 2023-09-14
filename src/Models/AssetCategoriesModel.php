<?php
namespace Src\Models;

use Error;

class AssetCategoriesModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new asset category
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function insertNewAssetsCategory($data, $created_by)
    {
        $statement = "INSERT INTO `assets_categories`(`assets_categories_name`, `created_by`) VALUES (:assets_categories_name,:created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $assets_categories_name = trim($data['assets_categories_name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_categories_name' => strtolower($assets_categories_name),
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset category by name
     * @param STRING $assets_categories_name
     * @return OBJECT $results
     */
    public function selectAssetsCategoryByName($assets_categories_name)
    {
        $statement = "SELECT * FROM `assets_categories` WHERE assets_categories_name = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assets_categories_name));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE assets category
     * @param NUMBER $assets_categories_id
     * @return OBJECT $results
     */
    public function selectAssetsCategoryById($assets_categories_id)
    {
        $statement = "SELECT * FROM `assets_categories` WHERE assets_categories_id = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assets_categories_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get All Assets Category
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllAssetsCategory()
    {
        $statement = "SELECT * FROM `assets_categories` WHERE `status` = ?";
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
     * update Assets Category
     * @param OBJECT $data, NUMBER $assets_categories_id
     * @return NUMBER $results
     */
    public function updateAssetsCategory($data, $assets_categories_id, $logged_user_id)
    {
        $statement = "UPDATE `assets_categories` SET `assets_categories_name`=:assets_categories_name, `status`=:status,`updated_by`=:updated_by WHERE `assets_categories_id`=:assets_categories_id";
        try {
            // Remove whitespaces from both sides of a string
            $assets_categories_name = trim($data['assets_categories_name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_categories_name' => strtolower($assets_categories_name),
                ':status' => $data['status'],
                ':updated_by' => $logged_user_id,
                ':assets_categories_id' => $assets_categories_id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
