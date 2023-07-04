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
      $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId
      = T.trainingId),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId";
      try {
          $statement = $this->db->query($statement);
          $statement->execute();
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          $result[0]['TrainingProviderlogo'] = '/trainingProviders/'.$result[0]['trainingProviderId'].'.jpg';
          unset($result[0]['trainingProviderId']);
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