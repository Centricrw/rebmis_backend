<?php
namespace Src\Models;

class QualificationsModel {

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
            qualifications;
      ";

      try {
          $statement = $this->db->query($statement);
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function findById($qualification_id)
    {
      $statement = "
          SELECT 
              *
          FROM
            qualifications WHERE qualification_id = ? LIMIT 1
      ";

      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($qualification_id));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

}
?>