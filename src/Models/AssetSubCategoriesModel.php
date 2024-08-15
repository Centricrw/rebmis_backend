<?php
namespace Src\Models;

use Error;

class AssetSubCategoriesModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new asset sub category
     * @param OBJECT $data
     * @param STRING $created_by
     * @return NUMBER
     */
    public function insertNewAssetsSubCategory($data, $created_by)
    {
        $statement = "INSERT INTO `assets_sub_categories`(`id`, `name`, `assets_categories_id`, `created_by`) VALUES (:id, :name, :assets_categories_id,:created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':name' => strtolower($assets_sub_categories_name),
                ':assets_categories_id' => $data['assets_categories_id'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset sub category by name
     * @param STRING $name
     * @return OBJECT $results
     */
    public function selectAssetsSubCategoryByName($name)
    {
        $statement = "SELECT * FROM `assets_sub_categories` WHERE `name` = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($name));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE assets sub category
     * @param NUMBER $id
     * @return OBJECT $results
     */
    public function selectAssetsSubCategoryById($id)
    {
        $statement = "SELECT * FROM `assets_sub_categories` WHERE id = ? LIMIT 1";
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
     * get All Assets sub Category
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllAssetsSubCategory()
    {
        $statement = "SELECT * FROM `assets_sub_categories` WHERE `status` = ?";
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
     * get All Assets sub Category by category id
     * @param NUMBER $assets_categories_id
     * @return OBJECT $results
     */
    public function selectAllAssetsCategoryByCategoryID($assets_categories_id)
    {
        $statement = "SELECT * FROM `assets_sub_categories` WHERE `assets_categories_id` = ? AND `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assets_categories_id, 1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update Assets Category
     * @param OBJECT $data
     * @param STRING $id
     * @param STRING $logged_user_id
     * @return NUMBER $results
     */
    public function updateAssetsSubCategory($data, $id, $logged_user_id)
    {
        $statement = "UPDATE `assets_sub_categories` SET `name`=:name,`assets_categories_id`=:assets_categories_id , `status`=:status,`updated_by`=:updated_by WHERE `id`=:id";
        try {
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':name' => strtolower($assets_sub_categories_name),
                ':assets_categories_id' => $data['assets_categories_id'],
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
