<?php
namespace Src\Models;

use Error;

class AssetCategoriesModel
{

    private $db = null;
    private $moodleDb = null;

    public function __construct($db, $moodleDb = null)
    {
        $this->db = $db;
        $this->moodleDb = $moodleDb;
    }
    /**
     * Create new asset category
     * @param {OBJECT} {data}
     * @return {VOID}
     */
    public function insertNewAssetsCategory($data, $created_by)
    {
        $statement = "INSERT INTO `assets_categories`(`assets_categories_name`, `attributes`, `checklist`, `created_by`) VALUES (:assets_categories_name, :attributes, :checklist,:created_by)";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_categories_name = trim($data['assets_categories_name']);
            $serialized_array = serialize($data['attributes']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_categories_name' => strtolower($assets_categories_name),
                ':attributes' => $serialized_array,
                ':checklist' => json_encode($data['checklist']),
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
        $statement = "UPDATE `assets_categories` SET `assets_categories_name`=:assets_categories_name, `attributes`=:attributes, `status`=:status, `checklist`=:checklist,`updated_by`=:updated_by WHERE `assets_categories_id`=:assets_categories_id";
        try {
            // Remove white spaces from both sides of a string
            $assets_categories_name = trim($data['assets_categories_name']);
            $serialized_array = serialize($data['attributes']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_categories_name' => strtolower($assets_categories_name),
                ':attributes' => $serialized_array,
                ':checklist' => json_encode($data['checklist']),
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
