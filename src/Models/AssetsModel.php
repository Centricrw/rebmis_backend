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
        $currentDate = date('Y-m-d');
        $statement = "INSERT INTO `assets`(`assets_id`, `name`, `serial_number`, `brand_id`, `assets_categories_id`, `assets_sub_categories_id`, `supplier_id`, `supplier_name`, `delivery_date`, `specification`, `created_by`) VALUES (:assets_id, :name, :serial_number, :brand_id, :assets_categories_id, :assets_sub_categories_id, :supplier_id, :supplier_name, :delivery_date, :specification, :created_by)";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_id' => $data['id'],
                ':name' => strtolower($assets_name),
                ':serial_number' => $data['serial_number'],
                ':brand_id' => $data['brand_id'],
                ':delivery_date' => isset($data['delivery_date']) ? $data['delivery_date'] : $currentDate,
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => isset($data['assets_sub_categories_id']) ? $data['assets_sub_categories_id'] : null,
                ':supplier_id' => isset($data['supplier_id']) ? $data['supplier_id'] : null,
                ':supplier_name' => isset($data['supplier_name']) ? $data['supplier_name'] : null,
                ':specification' => is_string($data['specification']) ? $data['specification'] : json_encode($data['specification']),
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    function usersHandler($value)
    {
        // 'STUDENTS', 'TEACHERS', 'STAFFS', 'SCHOOL'
        $user = strtolower(trim($value));
        if (strpos($user, "student") !== false) {
            return 'STUDENT';
        }
        if (strpos($user, "teacher") !== false) {
            return 'TEACHER';
        }
        if (strpos($user, "head") !== false) {
            return 'STAFF';
        }
        if (strpos($user, "staff") !== false) {
            return 'STAFF';
        }

        if (strpos($user, "head") !== false) {
            return 'HEAD TEACHER';
        }
        return 'SCHOOL';
    }

    function levelCodeHandler($level)
    {
        if ($level === "PRE PRIMARY" || $level === "PRE_PRIMARY") {
            return "3";
        }
        if ($level === "PRIMARY") {
            return "1";
        }
        if ($level === "SECONDARY") {
            return "2";
        }
        return "2";
    }

    /**
     * insert new migrated assets
     * @param OBJECT $data
     * @return VOID
     */
    public function insertMigratedAsset($data, $created_by)
    {
        $currentDate = date('Y-m-d');
        $statement = "INSERT INTO `assets`(`assets_id`, `name`, `serial_number`, `assets_tag`, `level_code`, `school_code`, `batch_details_id`, `brand_id`, `assets_categories_id`, `assets_sub_categories_id`, `specification`, `supplier_id`, `supplier_name`, `price`, `delivery_date`, `warrant_period`, `condition`, `distribution_date`, `users`, `created_by`, `asset_state`) VALUES (:assets_id,:name,:serial_number,:assets_tag,:level_code,:school_code,:batch_details_id,:brand_id,:assets_categories_id,:assets_sub_categories_id,:specification,:supplier_id, :supplier_name,:price,:delivery_date,:warrant_period,:condition,:distribution_date,:users,:created_by,:asset_state)";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_id' => $data['id'],
                ':name' => strtolower($assets_name),
                ':serial_number' => $data['serial_number'],
                ':assets_tag' => $data['assets_tag'],
                ':level_code' => $this->levelCodeHandler($data['level_code']),
                ':school_code' => $data['school_code'],
                ':batch_details_id' => isset($data['batch_details_id']) ? $data['batch_details_id'] : null,
                ':brand_id' => $data['brand_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => isset($data['assets_sub_categories_id']) ? $data['assets_sub_categories_id'] : null,
                ':specification' => json_encode($data['specification']),
                ':supplier_id' => isset($data['supplier_id']) ? $data['supplier_id'] : null,
                ':supplier_name' => isset($data['supplier_name']) ? $data['supplier_name'] : null,
                ':price' => isset($data['price']) ? (int) $data['price'] : null,
                ':delivery_date' => isset($data['delivery_date']) && $data['delivery_date'] !== "" ? $data['delivery_date'] : $currentDate,
                ':warrant_period' => isset($data['warrant_period']) ? (int) $data['warrant_period'] : null,
                ':condition' => isset($data['condition']) ? $data['condition'] : null,
                ':distribution_date' => isset($data['distribution_date']) && $data['distribution_date'] !== "" ? $data['distribution_date'] : $currentDate,
                ':users' => isset($data['users']) ? $this->usersHandler($data['users']) : "SCHOOL",
                ':created_by' => $created_by,
                ':asset_state' => "assigned",
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
            // Remove whiteSpaces from both sides of a string
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
     * checking if computer already assigned
     * @param OBJECT $data
     * @param STRING $updated_by
     * @return OBJECT
     */
    public function selectIfAssignedToSchoolBySerial($data)
    {
        if (isset($data['serial_number'])) {
            $statement = "SELECT * FROM `assets` WHERE `serial_number` = :serial_number AND `asset_state` = :asset_state";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    ':asset_state' => "assigned",
                    ':serial_number' => $data['serial_number'],
                ));
                $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return $results;
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        }
        $statement = "SELECT * FROM `assets` WHERE `assets_id` = :assets_id AND `asset_state` = :asset_state";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':asset_state' => "assigned",
                ':assets_id' => $data['assets_id'],
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
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
     * get level by level code
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

    /**
     * select Count assets category on school
     * @param NUMBER $schoolCode
     * @param NUMBER $categoriesId
     * @param STRING $subCategoriesId
     * @return OBJECT $results
     */
    public function selectCountCategoryOnSchool($schoolCode, $categoriesId, $subCategoriesId)
    {
        $statement = "SELECT * FROM `assets` WHERE `school_code` = :school_code AND `assets_categories_id` = :assets_categories_id AND `assets_sub_categories_id` = :assets_sub_categories_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":school_code" => $schoolCode,
                ":assets_categories_id" => $categoriesId,
                ":assets_sub_categories_id" => $subCategoriesId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select Count assets category on school
     *
     * @param NUMBER $categoriesId
     * @param STRING $subCategoriesId
     * @return OBJECT $results
     */
    public function selectCountCategoryOnREB($categoriesId, $subCategoriesId)
    {
        $statement = "SELECT COUNT(*) as `total` FROM `assets` WHERE `assets_categories_id` = :assets_categories_id AND `assets_sub_categories_id` = :assets_sub_categories_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":assets_categories_id" => $categoriesId,
                ":assets_sub_categories_id" => $subCategoriesId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * check if engraving code exists
     * @param STRING $engravingCode
     * @return OBJECT $results
     */
    public function selectAssetsByEngravingCodeLimit($engravingCode)
    {
        $statement = "SELECT * FROM `assets` WHERE `assets_tag` = :assets_tag LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":assets_tag" => $engravingCode,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * assign assets to school
     * @param OBJECT $data
     * @param NUMBER $id
     * @return NUMBER $results
     */
    public function assignAssetsToSchool($data, $logged_user_id)
    {
        $currentDate = date('Y-m-d');
        $statement = "UPDATE `assets` SET `assets_tag`=:assets_tag, `level_code`=:level_code, `school_code`=:school_code, `asset_state`=:asset_state, `users`=:users, `condition`=:condition, `batch_details_id`=:batch_details_id, `distribution_date`=:distribution_date, `updated_by`=:updated_by WHERE `assets_id`=:assets_id";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_tag' => $data['assets_tag'],
                ':level_code' => $data['level_code'],
                ':school_code' => $data['school_code'],
                ':batch_details_id' => $data['batch_details_id'],
                ':users' => $data['users'] ? $data['users'] : null,
                ':condition' => $data['condition'] ? $data['condition'] : "GOOD",
                ':distribution_date' => $currentDate,
                ':updated_by' => $logged_user_id,
                ":asset_state" => "assigned",
                ':assets_id' => $data['assets_id'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get ONE assets category
     * @param NUMBER $assets_categories_id
     * @return OBJECT $results
     */
    public function selectAssetsSchoolCategoryById($assets_categories_id, $school_code)
    {
        $statement = "SELECT ASSET.*, LEVELS.level_name as school_level_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name FROM `assets` ASSET
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = ASSET.assets_categories_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = ASSET.assets_sub_categories_id
        LEFT JOIN `levels` LEVELS ON ASSET.level_code = LEVELS.level_code
        WHERE ASSET.assets_categories_id = ? AND ASSET.school_code = ? ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assets_categories_id, $school_code));
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
    public function selectNotAssignedStockAssets($assets_sub_categories_id, $brand_id)
    {
        $statement = "SELECT ASSET.*, LEVELS.level_name as school_level_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name FROM `assets` ASSET
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = ASSET.assets_categories_id
        INNER JOIN `Brands` BA ON BA.id = ASSET.brand_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = ASSET.assets_sub_categories_id
        LEFT JOIN `levels` LEVELS ON ASSET.level_code = LEVELS.level_code
        WHERE ASSET.assets_sub_categories_id = ? AND ASSET.brand_id = ? AND ASSET.asset_state != ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($assets_sub_categories_id, $brand_id, 'assigned'));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get assets by user
     * @param STRING $logged_user_id
     * @return OBJECT $results
     */
    public function selectAssetsUploadedByUser($logged_user_id, $schoolCode, $page)
    {
        $results_per_page = 50;
        $page_first_result = ($page - 1) * $results_per_page;
        $queryCount = "SELECT COUNT(*) AS total_count FROM `assets` WHERE `created_by` = :created_by";

        $statement = "SELECT ASSET.*, S.school_name, LEVELS.level_name as school_level_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name FROM `assets` ASSET
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = ASSET.assets_categories_id
        INNER JOIN `Brands` BA ON BA.id = ASSET.brand_id
        INNER JOIN `schools` S ON S.school_code = ASSET.school_code
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = ASSET.assets_sub_categories_id
        LEFT JOIN `levels` LEVELS ON ASSET.level_code = LEVELS.level_code
        WHERE ASSET.created_by = :created_by AND ASSET.school_code = :school_code LIMIT " . $page_first_result . ',' . $results_per_page;
        try {

            $resultCount = $this->db->prepare($queryCount);
            $resultCount->execute(array(
                ":created_by" => $logged_user_id,
                ":school_code" => $schoolCode,
            ));
            $number_of_result = $resultCount->fetchAll(\PDO::FETCH_ASSOC);
            // determining the total number of pages available
            $number_of_page = ceil($number_of_result[0]['total_count'] / $results_per_page);

            $statement = $this->db->prepare($statement);
            $statement->execute(array(":created_by" => $logged_user_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

            return [
                "total_pages" => $number_of_page,
                "current_page" => $page,
                "assets" => $results,
            ];
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get assets by user
     * @param STRING $logged_user_id
     * @return OBJECT $results
     */
    public function selectAssetsForBatchOnShool($school_code, $batch_details_id)
    {
        $statement = "SELECT ASSET.*, S.school_name, LEVELS.level_name as school_level_name, AC.assets_categories_name, ASUB.name as assets_sub_categories_name FROM `assets` ASSET
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = ASSET.assets_categories_id
        INNER JOIN `Brands` BA ON BA.id = ASSET.brand_id
        INNER JOIN `schools` S ON S.school_code = ASSET.school_code
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = ASSET.assets_sub_categories_id
        LEFT JOIN `levels` LEVELS ON ASSET.level_code = LEVELS.level_code
        WHERE ASSET.batch_details_id = :batch_details_id AND ASSET.school_code= :school_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":batch_details_id" => $batch_details_id,
                ":school_code" => $school_code));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get assets by user
     * @param STRING $logged_user_id
     * @return OBJECT $results
     */
    public function selectAllEngravedAssets()
    {
        $statement = "SELECT assets.*, Brands.name as brand_name FROM `assets` LEFT JOIN Brands ON Brands.id = assets.brand_id WHERE assets.`assets_tag` LIKE '%REB-%' ORDER BY assets.create_at DESC";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get assets by user
     * @param STRING $logged_user_id
     * @return OBJECT $results
     */
    public function selectAssetsReceivedOnRebBYDate($date, $surlier_id)
    {
        // Set the start and end timestamps for filtering (modify as needed)
        $startDateTimeStamp = strtotime($date); // Convert to Unix timestamp
        $start_date = date("Y-m-d H:i:s", $startDateTimeStamp);
        $endDateTimeStamp = strtotime($date . " 23:59:59"); // Include the whole day
        $end_date = date("Y-m-d H:i:s", $endDateTimeStamp);

        // echo $start_date . " - " . $end_date;

        $statement = "SELECT ASSET.*, AC.assets_categories_name, ASUB.name as assets_sub_categories_name FROM `assets` ASSET
        INNER JOIN `assets_categories` AC ON AC.assets_categories_id = ASSET.assets_categories_id
        INNER JOIN `Brands` BA ON BA.id = ASSET.brand_id
        LEFT JOIN `assets_sub_categories` ASUB ON ASUB.id = ASSET.assets_sub_categories_id
        WHERE ASSET.create_at BETWEEN :startDate AND :endDate";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":startDate" => $start_date,
                ":endDate" => $end_date,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAssetsBySerialNumbers($serialNumbers)
    {
        if (empty($serialNumbers)) {
            return []; // Return an empty array if no supplier IDs are provided
        }
        $placeholders = implode(',', array_fill(0, count($serialNumbers), '?'));
        $statement = "SELECT * FROM `assets` WHERE `serial_number` IN ($placeholders)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($serialNumbers);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
