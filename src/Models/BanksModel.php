<?php
namespace Src\Models;

class BanksModel {

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
                  banks WHERE status = ?
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
  public function findById($bank_id)
  {
      $statement = "
          SELECT 
              *
          FROM
              banks WHERE bank_id=? AND status = ?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($bank_id,1));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>