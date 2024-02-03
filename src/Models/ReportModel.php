<?php
namespace Src\Models;

class ReportModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getGeneralReport()
    { 
      $statement = " SELECT * FROM general_report";
      try {
          $statement = $this->db->query($statement);
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
    public function markTheTrainee($data)
    {  
      $markType = $data['markType'];
      $statement = "UPDATE general_report SET $markType = :marks WHERE userId = :userId AND unitId = :unitId AND cohortId = :cohortId";
      try {
          $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":userId" => $data['userId'],
                ":cohortId" => $data['cohortId'],
                ":unitId" => $data['unitId'],
                ":marks" => $data['marks'],
            ));
            return $statement->rowCount();
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>