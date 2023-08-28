<?php
namespace Src\Models;

class CohortsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getAllCohorts($trainingId)
    {
      $statement = "SELECT C.cohortId, C.cohortName, C.cohortStart, C.cohortEnd, C.trainingId, IFNULL((SELECT capacity FROM cohortconditions CC WHERE CC.cohortId = C.cohortId),0) requestedTrainees, IFNULL((SELECT COUNT(T.traineesId) FROM trainees T WHERE T.cohortId = C.cohortId AND status = 'approved'),0) providedTrainees FROM cohorts C WHERE trainingId = ?";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($trainingId));
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $newResult = [];
        foreach($results as $result){
            array_push($newResult, $result);
        }
        
        return $newResult;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function addACohort($data, $user_id, $trainingId){
      $statement = "INSERT INTO cohorts (cohortName,cohortStart,cohortEnd,trainingId,createdBy) 
      VALUES(:cohortName,:cohortStart,:cohortEnd,:trainingId,:createdBy)";
        
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            ':cohortName' => $data['cohortName'],
            ':cohortStart' => $data['cohortStart'],
            ':cohortEnd' => $data['cohortEnd'],
            ':trainingId' => $trainingId,
            ':createdBy' => $user_id
        ));
        $data['cohortId']= $this->db->lastInsertId();
        $data['requestedTrainees']= '0';
        $data['providedTrainees']= '0';
        return $data;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
    }

}
?>