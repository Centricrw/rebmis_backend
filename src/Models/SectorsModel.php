<?php
namespace Src\Models;

class SectorsModel {

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
            sectors
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
  public function findByCode($sector_code)
  {
    $statement = "
        SELECT 
            *
        FROM
            sectors 
        WHERE sector_code=?
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($sector_code));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>