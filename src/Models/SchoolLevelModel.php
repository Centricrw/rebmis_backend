<?php
namespace Src\Models;

class SchoolLevelModel {

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
            school_levels WHERE status = ?
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
  public function findById($school_level_id)
  {
    $statement = "
        SELECT 
            *
        FROM
            school_levels WHERE school_level_id = ? AND status = ?
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($school_level_id,1));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>