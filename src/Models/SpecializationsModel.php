<?php
namespace Src\Models;

class SpecializationsModel {

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
                specializations WHERE status = ?
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
  public function findById($specialization_id)
  {
      $statement = "
          SELECT 
            *
          FROM
            specializations WHERE specialization_id=? AND status = ?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($specialization_id,1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>