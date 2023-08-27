<?php
namespace Src\Models;

class AcademicCalenderModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function findAll()
    {
      $statement = " 
        SELECT  * FROM academic_calendar ";

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
      $statement = "SELECT * FROM academic_calendar WHERE academic_year_status = 'true'";
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