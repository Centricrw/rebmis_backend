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
        WHERE assets_request_status != :approved_request_status AND assets_request_status != :rejected_request_status AND school_code = :school_code AND category_id = :category_id AND subcategory_id = :subcategory_id
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

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @return OBJECT
     */
    public function getSchoolRequestAssetById($assets_request_id)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request.assets_request_id = :assets_request_id
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $assets_request_id,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new asset
     * @param OBJECT $data
     * @return VOID
     */
    public function addSchoolToVisitingList($data, $created_by)
    {
        $currentDate = date('Y-m-d');
        $statement = "INSERT INTO `assets_request_details`(`assets_request_details_id`, `assets_request_id`, `visit_time`, `created_by`) VALUES (:assets_request_details_id, :assets_request_id, :visit_time, :created_by)";
        try {
            // Remove whiteSpaces from both sides of a string
            $assets_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_details_id' => $data['assets_request_details_id'],
                ':assets_request_id' => $data['assets_request_id'],
                ':visit_time' => $data['visit_time'],
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @return OBJECT
     */
    public function getSchoolVisitingList()
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, assets_request_details.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN assets_request_details ON assets_request_details.assets_request_id = assets_request.assets_request_id
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request_details.school_is_visited = :school_is_visited
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':school_is_visited' => "PENDING",
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @return OBJECT
     */
    public function getSchoolVisitingListBySchoolCode($school_code)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, assets_request_details.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN assets_request_details ON assets_request_details.assets_request_id = assets_request.assets_request_id
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request_details.school_is_visited = :school_is_visited AND assets_request.school_code = :school_code
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':school_is_visited' => "PENDING",
                ':school_code' => $school_code,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @return OBJECT
     */
    public function checkingIfSchoolOnVisitingList($assets_request_id)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT * FROM `assets_request_details` WHERE `assets_request_id`=:assets_request_id AND `school_is_visited`=:school_is_visited ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $assets_request_id,
                ':school_is_visited' => "PENDING",
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @param STRING $value
     * @return OBJECT
     */
    public function requestForAVisit($assets_request_id, $value)
    {
        $currentDate = date('Y-m-d');
        $statement = "UPDATE `assets_request` SET `is_requesting_visit`=:is_requesting_visit WHERE `assets_request_id`=:assets_request_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $assets_request_id,
                ':is_requesting_visit' => $value,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Requested for a visit
     * @return OBJECT
     */
    public function getSchoolRequestedForVisit()
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request.is_requesting_visit = :is_requesting_visit
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':is_requesting_visit' => "1",
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Requested for a visit
     * @return OBJECT
     */
    public function confirmSchoolRequestAssets($data, $created_by)
    {
        $currentDate = date('Y-m-d');
        $statement = "UPDATE `assets_request_details` SET `school_is_visited`=:school_is_visited, `action_done`=:action_done, `approved_checklist`=:approved_checklist, `reason`=:reason, `return_time`=:return_time, `visited_by`=:visited_by, `visited_date`=:visited_date WHERE `assets_request_details_id`=:assets_request_details_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_details_id' => $data['assets_request_details_id'],
                ':school_is_visited' => "DONE",
                ':action_done' => $data['action_done'],
                ':approved_checklist' => json_encode($data['approved_checklist']),
                ':reason' => $data['reason'],
                ':return_time' => isset($data['return_time']) ? $data['return_time'] : null,
                ':visited_by' => $created_by,
                ':visited_date' => $currentDate,

            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_details_id
     * @return OBJECT
     */
    public function getSchoolVisitingListBYid($assets_request_details_id)
    {
        $currentDate = date('Y-m-d');
        $statement = "SELECT assets_request.*, assets_request_details.*, schools.school_name, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name FROM `assets_request`
        INNER JOIN assets_request_details ON assets_request_details.assets_request_id = assets_request.assets_request_id
        INNER JOIN schools ON schools.school_code = assets_request.school_code
        INNER JOIN assets_categories ON assets_categories.assets_categories_id = assets_request.category_id
        INNER JOIN assets_sub_categories ON assets_sub_categories.id = assets_request.subcategory_id
        WHERE assets_request_details.assets_request_details_id = :assets_request_details_id
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_details_id' => $assets_request_details_id,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @param STRING $value
     * @return OBJECT
     */
    public function confirmSchoolRequest($assets_request_id, $value)
    {
        $statement = "UPDATE `assets_request` SET `assets_request_status`=:assets_request_status, `is_requesting_visit`=:is_requesting_visit WHERE `assets_request_id`=:assets_request_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $assets_request_id,
                ':assets_request_status' => $value,
                ':is_requesting_visit' => "0",
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get School Request Asset By Id
     * @param STRING $assets_request_id
     * @return OBJECT
     */
    public function getSchoolVisitingReport($assets_request_id)
    {
        $statement = "SELECT * FROM `assets_request_details` WHERE assets_request_id = :assets_request_id AND school_is_visited=:school_is_visited
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':assets_request_id' => $assets_request_id,
                ':school_is_visited' => "DONE",
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                $results[$key]['approved_checklist'] = json_decode($value['approved_checklist']);
            }
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateServerAssetsRequest($assets_request_id, $confirm_status)
    {
        if (empty($assets_request_id)) {
            return []; // Return an empty array if no supplier IDs are provided
        }
        $placeholders = implode(',', array_fill(0, count($assets_request_id), '?'));
        $statement = "UPDATE `assets_request` SET assets_request_status = ? WHERE `assets_request_id` IN ($placeholders)";
        $mergedArray = array_merge([$confirm_status], $assets_request_id);
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($mergedArray);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
