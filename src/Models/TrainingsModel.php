<?php
namespace Src\Models;

use Error;

class TrainingsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function addTrainingProviderlogo($trainingsArray)
    {
        $newTrainingsArray = array();
        foreach ($trainingsArray as $trainingItem) {
            $trainingItem['TrainingProviderlogo'] = '/trainingProviders/' . $trainingItem['trainingProviderId'] . '.jpg';
            $newTrainingsArray[] = $trainingItem;
        }
        return $newTrainingsArray;
    }

    public function getAllTranings($userDistrictCode)
    {
        if (isset($userDistrictCode) && $userDistrictCode !== "") {
            $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId INNER JOIN cohorts C ON T.trainingId = C.trainingId INNER JOIN cohortconditions CND ON C.cohortId = CND.cohortId WHERE CND.district_code = $userDistrictCode";
        } else {
            $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId";
        }
        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (sizeof($result) > 0) {
                $result = $this->addTrainingProviderlogo($result);
            }
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTrainingsTrainees($training_id, $cohort_id)
    {
        $statement = "SELECT trainees.*, trainings.trainingName FROM trainees
        INNER JOIN trainings ON trainees.trainingId = trainings.trainingId
        WHERE trainees.trainingId = :trainingId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':trainingId' => $training_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getOneTraining($training_id)
    {
        $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId
      = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId WHERE T.trainingId = :trainingId LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':trainingId' => $training_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (sizeof($result) > 0) {
                $result = $this->addTrainingProviderlogo($result)[0];
            }
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
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
            throw new Error($e->getMessage());
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
        $statement = "INSERT INTO trainings (trainingName,trainingDescription,offerMode,trainingProviderId,startDate,endDate,createdBy)
      VALUES(:trainingName,:trainingDescription,:offerMode,:trainingProviderId,:startDate,:endDate,:createdBy)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingName' => $data['trainingName'],
                ':trainingDescription' => $data['trainingDescription'],
                ':offerMode' => $data['offerMode'],
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

    public function getTrainingProvider()
    {
        $sql = "SELECT * FROM trainingProviders";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array());

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function CreateTrainingProvider($data, $doneBY)
    {
        $statement = "INSERT INTO `trainingProviders`(`trainingProviderName`, `description`, `email`, `address`, `phone_number`, `supporting_documents`, `createdBy`) VALUES (:trainingProviderName,:description,:email,:address,:phone_number,:supporting_documents,:createdBy)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingProviderName' => $data->trainingProviderName,
                ':description' => $data->description,
                ':email' => $data->email,
                ':address' => $data->address,
                ':phone_number' => $data->phone_number,
                ':supporting_documents' => $data->supporting_documents,
                ':createdBy' => $doneBY,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function ProviderExists($data)
    {
        $sql = "SELECT trainingProviderName, email, phone_number FROM trainingProviders WHERE trainingProviderName = :trainingProviderName OR email = :email OR phone_number = :phone_number LIMIT 1";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':trainingProviderName' => $data['trainingProviderName'],
                ':phone_number' => $data['phone_number'],
                ':email' => $data['email'],
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
