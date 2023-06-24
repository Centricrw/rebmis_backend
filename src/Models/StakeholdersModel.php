<?php
namespace Src\Models;

class StakeholdersModel {

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
            stakeholders
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
  public function findByCode($stakeholder_id)
  {
    $statement = "
        SELECT 
            *
        FROM
            stakeholders 
        WHERE stakeholder_id=?
    ";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($stakeholder_id));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>