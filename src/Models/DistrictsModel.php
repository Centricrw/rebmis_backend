<?php
namespace Src\Models;

class DistrictsModel {

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
            districts
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
  public function findByCode($district_code)
  {
    $statement = "
        SELECT 
            *
        FROM
            districts WHERE district_code=?
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($district_code));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>