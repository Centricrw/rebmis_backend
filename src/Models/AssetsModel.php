<?php
namespace Src\Models;

use Error;

class AssetsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new asset
     * @param OBJECT $data
     * @return VOID
     */
    public function insertNewAsset($data, $created_by)
    {
        $statement = "INSERT INTO `assets`(`assets_id`, `name`, `serial_number`, `brand_id`, `assets_categories_id`, `assets_sub_categories_id`, `specification`, `created_by`) VALUES (:assets_id, :name, :serial_number, :brand_id, :assets_categories_id, :assets_sub_categories_id, :specification, :created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_id' => $data['id'],
                ':name' => strtolower($assets_name),
                ':serial_number' => $data['serial_number'],
                ':brand_id' => $data['brand_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => isset($data['assets_sub_categories_id']) ? $data['assets_sub_categories_id'] : null,
                ':specification' => json_encode($data['specification']),
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset by serial number
     * @param STRING $serialNumber
     * @return OBJECT $results
     */
    public function selectAssetsBySerialNumber($serialNumber)
    {
        $statement = "SELECT A.assets_id, A.name, A.serial_number, C.assets_categories_id, C.assets_categories_name, A.assets_sub_categories_id, SC.name as assets_sub_categories_name, A.brand_id, B.name as brand_name, A.specification  FROM `assets` A
        INNER JOIN `assets_categories` C ON A.assets_categories_id = C.assets_categories_id
        INNER JOIN `assets_sub_categories` SC ON A.assets_sub_categories_id = SC.id
        INNER JOIN `Brands` B ON A.brand_id = B.id
        WHERE A.serial_number = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($serialNumber));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset by category, brand, subcategory
     * @param OBJECT $values
     * @return OBJECT $results
     */
    public function selectAssetsByCategoryBrandSubCategory($values)
    {
        $statement = "SELECT A.assets_id, A.name, A.serial_number, C.assets_categories_id, C.assets_categories_name, A.assets_sub_categories_id, SC.name as assets_sub_categories_name, A.brand_id, B.name as brand_name, A.specification  FROM `assets` A
        INNER JOIN `assets_categories` C ON A.assets_categories_id = C.assets_categories_id
        LEFT JOIN `assets_sub_categories` SC ON A.assets_sub_categories_id = SC.id
        INNER JOIN `Brands` B ON A.brand_id = B.id
        WHERE A.assets_categories_id = :assets_categories_id AND A.assets_sub_categories_id = :assets_sub_categories_id AND A.brand_id = :brand_id AND A.asset_state = :asset_state";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_categories_id' => $values['assets_categories_id'],
                ':assets_sub_categories_id' => $values['assets_sub_categories_id'],
                ':brand_id' => $values['brand_id'],
                ':asset_state' => "available",
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset by category
     * @param STRING $serialNumber
     * @return OBJECT $results
     */
    public function selectAssetsByCategory($assetsCategoriesId)
    {
        $statement = "SELECT A.assets_id, A.name, A.serial_number, C.assets_categories_id, C.assets_categories_name, A.assets_sub_categories_id, SC.name as assets_sub_categories_name, A.brand_id, B.name as brand_name, A.specification  FROM `assets` A
        INNER JOIN `assets_categories` C ON A.assets_categories_id = C.assets_categories_id
        LEFT JOIN `assets_sub_categories` SC ON A.assets_sub_categories_id = SC.id
        INNER JOIN `Brands` B ON A.brand_id = B.id
        WHERE A.assets_categories_id = ? AND A.asset_state = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assetsCategoriesId, "available"));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE asset
     * @param NUMBER $id
     * @return OBJECT $results
     */
    public function selectAssetById($id)
    {
        $statement = "SELECT * FROM `assets` WHERE assets_id = ? LIMIT 1";
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
     * get All Assets
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllAssets()
    {
        $statement = "SELECT A.assets_id, A.name, A.serial_number, C.assets_categories_id, C.assets_categories_name, A.assets_sub_categories_id, SC.name as assets_sub_categories_name, A.brand_id, B.name as brand_name, A.specification  FROM `assets` A
        INNER JOIN `assets_categories` C ON A.assets_categories_id = C.assets_categories_id
        INNER JOIN `assets_sub_categories` SC ON A.assets_sub_categories_id = SC.id
        INNER JOIN `Brands` B ON A.brand_id = B.id WHERE A.`status` = ?";
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
     * update Asset
     * @param OBJECT $value
     * @param STRING $logged_user_id
     * @param STRING $state
     * @return VOID
     */
    public function bookAssetStateByCategory($value, $logged_user_id, $state = "booked")
    {
        $limit = (int) $value['assets_number_limit'];
        $current = $state == "booked" ? "available" : "booked";
        $statement = "UPDATE `assets` SET `asset_state`=:asset_state, `updated_by`=:updated_by WHERE `assets_categories_id`=:assets_categories_id AND `assets_sub_categories_id`=:assets_sub_categories_id AND `brand_id`=:brand_id AND `asset_state` = :current LIMIT $limit";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':asset_state' => $state,
                ':current' => $current,
                ':updated_by' => $logged_user_id,
                ':assets_categories_id' => $value['assets_categories_id'],
                ':assets_sub_categories_id' => $value['assets_sub_categories_id'],
                ':brand_id' => $value['brand_id'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update Asset
     * @param OBJECT $data
     * @param NUMBER $id
     * @return NUMBER $results
     */
    public function updateAsset($data, $id, $logged_user_id)
    {
        $statement = "UPDATE `assets` SET `name`=:name, `serial_number`=:serial_number, `brand_id`=:brand_id, `assets_categories_id`=:assets_categories_id, `assets_sub_categories_id`=:assets_sub_categories_id, `specification`=:specification, `status`=:status,`updated_by`=:updated_by WHERE `assets_id`=:assets_id";
        try {
            // Remove whitespaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':name' => strtolower($assets_name),
                ':serial_number' => $data['serial_number'],
                ':brand_id' => $data['brand_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => isset($data['assets_sub_categories_id']) ? $data['assets_sub_categories_id'] : null,
                ':specification' => json_encode($data['specification']),
                ':status' => $data['status'],
                ':updated_by' => $logged_user_id,
                ':assets_id' => $id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
