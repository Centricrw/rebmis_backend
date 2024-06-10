<?php
namespace Src\Models;

use Error;

class AssetsRequestModel
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
    public function insertNewRequestAsset($data, $created_by)
    {
        $currentDate = date('Y-m-d');
        $statement = "INSERT INTO `assets_request`(`assets_request_id`, `school_code`, `category_id`, `subcategory_id`, `assets_number`, `users`, `reason`, `checklist`, `created_by`) VALUES (:assets_request_id, :school_code, :category_id, :subcategory_id, :assets_number, :users,:reason,:checklist,:created_by)";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $data['assets_request_id'],
                ':school_code' => $data['school_code'],
                ':category_id' => $data['category_id'],
                ':subcategory_id' => $data['subcategory_id'],
                ':assets_number' => $data['assets_number'],
                ':users' => $data['users'],
                ':reason' => $data['reason'],
                ':checklist' => json_encode($data['checklist']),
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new asset
     * @param
     * @return OBJECT
     */
    public function getAllRequestAsset()
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new asset
     * @param OBJECT $data
     * @return OBJECT
     */
    public function getSchoolRequestAsset($school_code)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request.school_code = :school_code
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':school_code' => $school_code,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new asset
     * @param OBJECT $data
     * @return OBJECT
     */
    public function getSchoolRequestAssetByStatus($assets_request_status)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request.assets_request_status = :assets_request_status
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_status' => $assets_request_status,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new asset
     * @param OBJECT $data
     * @return OBJECT
     */
    public function checkSchoolHasPendingOrReturnedRequest($data)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT * FROM `assets_request`
        WHERE assets_request_status != :approved_request_status OR assets_request_status != :rejected_request_status AND school_code = :school_code AND category_id = :category_id AND subcategory_id = :subcategory_id
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':approved_request_status' => "APPROVED",
                ':rejected_request_status' => "REJECTED",
                ':school_code' => $data['school_code'],
                ':category_id' => $data['category_id'],
                ':subcategory_id' => $data['subcategory_id'],
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
