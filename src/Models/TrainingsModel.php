<?php
namespace Src\Models;

class TrainingsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getAllTranings()
    {
      $statement = "SELECT  * FROM trainings";

      try {
          $statement = $this->db->query($statement);
          $statement->execute();
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function findCurrentAcademicYear()
    {
      $statement = "SELECT * FROM academic_calendar WHERE status = 1";

      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array());
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(sizeof($result) == 0){
          return null;
        }
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>