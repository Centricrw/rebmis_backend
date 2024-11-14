<?php
namespace Src\Models;

class StaffCategoryModel {

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
                staff_category WHERE status = ?
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
  public function findById($staff_category_id)
  {
      $statement = "
          SELECT 
            *
          FROM
            staff_category WHERE staff_category_id=? AND status = ?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($staff_category_id,1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>