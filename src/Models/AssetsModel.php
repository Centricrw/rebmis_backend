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
        $statement = "SELECT A.assets_id, A.name, A.serial_number, A.asset_state, C.assets_categories_id, C.assets_categories_name, A.assets_sub_categories_id, SC.name as assets_sub_categories_name, A.brand_id, B.name as brand_name, A.specification  FROM `assets` A
        INNER JOIN `assets_categories` C ON A.assets_categories_id = C.assets_categories_id
        LEFT JOIN `assets_sub_categories` SC ON A.assets_sub_categories_id = SC.id
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
     * get asset by asset tag
     * @param STRING $serialNumber
     * @return OBJECT $results
     */
    public function selectAssetsByTag($assetTag)
    {
        $statement = "SELECT * FROM `assets` WHERE assets_tag = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assetTag));
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

    /**
     * Insert new asset to school
     * @param OBJECT $data
     * @param STRING $updated_by
     * @return VOID
     */
    public function insertNewAssetsToSchool($data, $updated_by)
    {
        $statement = "UPDATE `assets` SET `assets_tag` = :assets_tag, `school_code` = :school_code, `level_code` = :level_code, `asset_state` = :asset_state, `updated_by` = :updated_by WHERE `serial_number` = :serial_number";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_tag' => $data['assets_tag'],
                ':school_code' => $data['school_code'],
                ':level_code' => $data['level_code'],
                ':asset_state' => "assigned",
                ':updated_by' => $updated_by,
                ':serial_number' => $data['serial_number'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
    /**
     * get school by school code
     * @param STRING $schoolCode
     * @return ARRAY
     */
    public function getSchoolAssetsBySchoolCode($schoolCode)
    {
        $statement = "SELECT A.*, AC.assets_categories_name, ASUB.name as assets_sub_categories_name, Br.name as brand_name FROM `assets` A
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = A.assets_categories_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = A.assets_sub_categories_id
        INNER JOIN `Brands` Br ON Br.id = A.brand_id
        WHERE A.`school_code` = :school_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':school_code' => $schoolCode));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get school by school code
     * @param STRING $schoolCode
     * @return ARRAY
     */
    public function getSchoolByCode($schoolCode)
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
     * get school assets number
     * @param OBJECT $data
     * @return ARRAY
     */
    public function getSchoolSchoolAssets($data)
    {
        $statement = "SELECT A.*, L.level_name, S.school_name  FROM `assets` A
        INNER JOIN `schools` S ON S.school_code = A.school_code
        INNER JOIN `levels` L ON L.level_code = A.level_code
        WHERE A.`school_code` = :school_code AND A.`level_code` = :level_code AND A.`assets_categories_id` = :assets_categories_id AND A.`assets_sub_categories_id` = :assets_sub_categories_id AND A.`brand_id` = :brand_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":school_code" => $data['school_code'],
                ":level_code" => $data['level_code'],
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
     * get levl by level code
     * @param STRING $levelCode
     * @return ARRAY
     */
    public function getLevelByCode($levelCode)
    {
        $statement = "SELECT * FROM `levels` WHERE `level_code` = :level_code LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":level_code" => $levelCode));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
