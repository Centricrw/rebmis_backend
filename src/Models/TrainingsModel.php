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

    public function addAtraining($data, $user_id){
      $statement = "INSERT INTO trainings (trainingName,trainingDescription,trainingProviderId,startDate,endDate,createdBy) 
      VALUES(:trainingName,:trainingDescription,:trainingProviderId,:startDate,:endDate,:createdBy)";
      
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            ':trainingName' => $data['trainingName'],
            ':trainingDescription' => $data['trainingDescription'],
            ':trainingProviderId' => $data['trainingProviderId'],
            ':startDate' => $data['startDate'],
            ':endDate' => $data['endDate'],
            ':createdBy' => $user_id
        ));
        $inserted = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $inserted;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
    }

}
?>