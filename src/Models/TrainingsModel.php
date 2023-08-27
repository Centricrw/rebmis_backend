<?php
namespace Src\Models;

class TrainingsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllTranings()
    {
        $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId = T.trainingId),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId";
        try
        {
            $statement = $this->db->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $result[0]['TrainingProviderlogo'] = '/trainingProviders/' . $result[0]['trainingProviderId'] . '.jpg';
            unset($result[0]['trainingProviderId']);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findCurrentAcademicYear()
    {
        $statementSelectAcademic = "SELECT * FROM academic_calendar WHERE status = 1";

        try {
            $statementSelectAcademic = $this->db->prepare($statementSelectAcademic);
            $statementSelectAcademic->execute(array());
            $result = $statementSelectAcademic->fetchAll(\PDO::FETCH_ASSOC);
            if (sizeof($result) == 0) {
                return null;
            }
            $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId
      = T.trainingId),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId";
            $statement = $this->db->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $result[0]['TrainingProviderlogo'] = '/trainingProviders/' . $result[0]['trainingProviderId'] . '.jpg';
            unset($result[0]['trainingProviderId']);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getTraningsByStatus($status)
    {
        $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.trainingDescription, T.startDate, T.endDate, T.status,
        ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId
      = T.trainingId),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId WHERE T.status = ? ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findTrainingByID($training_id)
    {
        $sql = "SELECT * FROM trainings WHERE trainingId = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($training_id));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function comfirmTraining($training_id, $status)
    {
        $sql = "UPDATE trainings SET status=:status WHERE trainingId = :trainingId";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':status' => $status,
                ':trainingId' => $training_id));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function addAtraining($data, $user_id)
    {
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
                ':createdBy' => $user_id,
            ));
            $data['trainingId'] = $this->db->lastInsertId();
            $data['TrainingProviderlogo'] = '/trainingProviders/' . $data['trainingProviderId'] . '.jpg';
            $data['trainees'] = '0';
            $data['status'] = 'Waiting';

            return $data;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
