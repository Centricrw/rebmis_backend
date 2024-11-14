<?php
namespace Src\Models;

use Error;

class BrandsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new asset brand
     * @param OBJECT $data
     * @param STRING $created_by
     * @return NUMBER
     */
    public function insertNewBrand($data, $created_by)
    {
        $statement = "INSERT INTO `Brands`(`id`, `name`, `created_by`) VALUES (:id, :name,:created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':id' => $data['id'],
                ':name' => strtolower($assets_sub_categories_name),
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get asset brand by name
     * @param STRING $name
     * @return OBJECT $results
     */
    public function selectBrandsByName($name)
    {
        $statement = "SELECT * FROM `Brands` WHERE `name` = ? LIMIT 1";
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
     * get ONE Brand
     * @param NUMBER $id
     * @return OBJECT $results
     */
    public function selectBrandsById($id)
    {
        $statement = "SELECT * FROM `Brands` WHERE id = ? LIMIT 1";
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
     * get All Brands
     * @param NULL
     * @return OBJECT $results
     */
    public function selectAllBrands()
    {
        $statement = "SELECT * FROM `Brands` WHERE `status` = ?";
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
     * update Brands
     * @param OBJECT $data
     * @param STRING $id
     * @param STRING $logged_user_id
     * @return NUMBER $results
     */
    public function updateBrand($data, $id, $logged_user_id)
    {
        $statement = "UPDATE `Brands` SET `name`=:name, `status`=:status,`updated_by`=:updated_by WHERE `id`=:id";
        try {
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':name' => strtolower($assets_sub_categories_name),
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
