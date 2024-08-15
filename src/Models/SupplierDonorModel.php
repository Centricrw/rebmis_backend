<?php
namespace Src\Models;

use Error;

class SupplierDonorModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function selectAll()
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE status = ? ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
    public function selectById($id)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE id=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectByUser_id($user_id)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE user_id=? limit 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectByName($name)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE name=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($name));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectByType($type)
    {
        $statement = "SELECT * FROM supplierDonor_tbl WHERE type=?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($type));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function insertNewSupplier($data, $logged_user_id)
    {
        $statement = "INSERT INTO `supplierDonor_tbl`(`id`, `user_id`, `name`, `institution`, `description`, `created_by`, `type`) VALUES (:id, :user_id, :name, :institution,:description,:created_by,:type)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':user_id' => $data['user_id'],
                ':name' => strtolower(trim($data['name'])),
                ':institution' => isset($data['institution']) ? $data['institution'] : null,
                ':description' => isset($data['description']) ? $data['description'] : null,
                ':created_by' => $logged_user_id,
                ':type' => $data['type'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function insertNewSuppliedAssets($data, $logged_user_id)
    {
        $statement = "INSERT INTO `supplied_assets`(`supplied_assets_id`, `name`, `short_description`, `serial_number`, `batch_details_id`, `brand_id`, `assets_categories_id`, `assets_sub_categories_id`, `specification`, `created_by`,`supplier_id`, `supplier_name`, `price`, `delivery_date`, `warrant_period`, `condition`, `users`, `currency`) VALUES (:supplied_assets_id, :name, :short_description, :serial_number,:batch_details_id, :brand_id, :assets_categories_id, :assets_sub_categories_id,:specification, :created_by, :supplier_id, :supplier_name,:price, :delivery_date, :warrant_period, :condition, :users, :currency)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':supplied_assets_id' => $data['supplied_assets_id'],
                ':name' => $data['name'],
                ':short_description' => $data['name'],
                ':serial_number' => $data['serial_number'],
                ':batch_details_id' => isset($data['batch_details_id']) ? $data['batch_details_id'] : null,
                ':brand_id' => $data['brand_id'],
                ':assets_categories_id' => $data['assets_categories_id'],
                ':assets_sub_categories_id' => $data['assets_sub_categories_id'],
                ':specification' => json_encode($data['specification']),
                ':created_by' => $logged_user_id,
                ':supplier_id' => $data['supplier_id'],
                ':supplier_name' => $data['supplier_name'],
                ':price' => $data['price'],
                ':delivery_date' => $data['delivery_date'],
                ':warrant_period' => $data['warrant_period'],
                ':condition' => $data['condition'],
                ':users' => isset($data['users']) ? $data['users'] : null,
                ':currency' => $data['currency'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateSupplier($data)
    {
        $statement = "UPDATE `supplierDonor_tbl` SET `name`=:name, `user_id`=:user_id,`institution`=:institution,`description`=:description,`status`=:status,`type`=:type WHERE `id`=:id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':user_id' => $data['user_id'],
                ':name' => strtolower(trim($data['name'])),
                ':institution' => isset($data['institution']) ? $data['institution'] : null,
                ':description' => isset($data['description']) ? $data['description'] : null,
                ':status' => $data['status'],
                ':type' => $data['type'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAssetsUploadedBYuser($user_id, $start_date, $end_date)
    {
        $statement = "SELECT supplied_assets.*, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name, Brands.name as brand_name FROM supplied_assets
        INNER JOIN `assets_categories` ON supplied_assets.assets_categories_id = assets_categories.assets_categories_id
        LEFT JOIN `assets_sub_categories` ON supplied_assets.assets_sub_categories_id = assets_sub_categories.id
        INNER JOIN `Brands` ON supplied_assets.brand_id = Brands.id
        WHERE supplied_assets.created_by = :created_by AND supplied_assets.create_at BETWEEN :start_date AND :end_date";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':created_by' => $user_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAssetsUploadedBYInstitution($supplier_id, $start_date, $end_date, $status = 'PENDING', $page = 1)
    {
        $results_per_page = 50;
        $page_first_result = ($page - 1) * $results_per_page;
        $queryCount = "SELECT COUNT(*) AS total_count FROM `supplied_assets` WHERE confirm_status = :confirm_status AND supplier_id = :supplier_id AND create_at BETWEEN :start_date AND :end_date";

        $statement = "SELECT supplied_assets.*, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name, Brands.name as brand_name FROM supplied_assets
        INNER JOIN `assets_categories` ON supplied_assets.assets_categories_id = assets_categories.assets_categories_id
        LEFT JOIN `assets_sub_categories` ON supplied_assets.assets_sub_categories_id = assets_sub_categories.id
        INNER JOIN `Brands` ON supplied_assets.brand_id = Brands.id
        WHERE supplied_assets.confirm_status = :confirm_status AND supplied_assets.supplier_id = :supplier_id AND supplied_assets.create_at BETWEEN :start_date AND :end_date LIMIT " . $page_first_result . ',' . $results_per_page;
        try {
            $resultCount = $this->db->prepare($queryCount);
            $resultCount->execute(array(
                ':confirm_status' => $status,
                ':supplier_id' => $supplier_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
            ));
            $number_of_result = $resultCount->fetchAll(\PDO::FETCH_ASSOC);
            // determining the total number of pages available
            $number_of_page = ceil($number_of_result[0]['total_count'] / $results_per_page);

            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':confirm_status' => $status,
                ':supplier_id' => $supplier_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return [
                "total_pages" => $number_of_page,
                "current_page" => $page,
                "total_assets" => $number_of_result[0]['total_count'],
                "assets" => $results,
            ];
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getSuppliedAssetsByIds($supplier_ids, $status = 'APPROVED')
    {
        if (empty($supplier_ids)) {
            return []; // Return an empty array if no supplier IDs are provided
        }
        $placeholders = implode(',', array_fill(0, count($supplier_ids), '?'));
        $statement = "SELECT * FROM `supplied_assets` WHERE confirm_status != ? AND `supplied_assets_id` IN ($placeholders)";
        $mergedArray = array_merge([$status], $supplier_ids);
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($mergedArray);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getSuppliedAssetsBySupplierID($supplier_ids, $status = 'PENDING')
    {
        if (empty($supplier_ids)) {
            return []; // Return an empty array if no supplier IDs are provided
        }
        $statement = "SELECT supplied_assets.*, assets_categories.assets_categories_name, assets_sub_categories.name as assets_sub_categories_name, Brands.name as brand_name FROM `supplied_assets`
        INNER JOIN `assets_categories` ON supplied_assets.assets_categories_id = assets_categories.assets_categories_id
        LEFT JOIN `assets_sub_categories` ON supplied_assets.assets_sub_categories_id = assets_sub_categories.id
        INNER JOIN `Brands` ON supplied_assets.brand_id = Brands.id
        WHERE supplied_assets.`confirm_status` = :confirm_status AND supplied_assets.`supplier_id` = :supplier_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                "supplier_id" => $supplier_ids,
                "confirm_status" => $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function confirmSuppliedAssetsByIds($supplier_ids, $confirm_status)
    {
        if (empty($supplier_ids)) {
            return []; // Return an empty array if no supplier IDs are provided
        }
        $placeholders = implode(',', array_fill(0, count($supplier_ids), '?'));
        $statement = "UPDATE `supplied_assets` SET confirm_status = ? WHERE `supplied_assets_id` IN ($placeholders)";
        $mergedArray = array_merge([$confirm_status], $supplier_ids);
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
