<?php
namespace Src\Models;

class EducationDomainModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
    public function findAll()
    {
        $statement = "
            SELECT 
                *
            FROM
                education_domain WHERE status = ?
        ";
        try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findById($education_domain_id)
    {
        $statement = "
            SELECT 
                *
            FROM
                education_domain WHERE education_domain_id=? AND status = ?
        ";
        try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($education_domain_id,1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
?>