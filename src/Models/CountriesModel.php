<?php
namespace Src\Models;

class CountriesModel {

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
                  countries WHERE status = ?
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
  public function findById($country_id)
  {
      $statement = "
          SELECT 
              *
          FROM
            countries WHERE country_id=? AND status = ?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($country_id,1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>