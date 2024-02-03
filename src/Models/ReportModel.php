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
}
?>