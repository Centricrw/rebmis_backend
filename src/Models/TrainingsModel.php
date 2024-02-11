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

    public function getAllTranings($userDistrictCode)
    {
        if (isset($userDistrictCode) && $userDistrictCode !== "") {
            $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, TP.TrainingProviderlogo, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId INNER JOIN cohorts C ON T.trainingId = C.trainingId INNER JOIN cohortconditions CND ON C.cohortId = CND.cohortId WHERE CND.district_code = $userDistrictCode GROUP BY T.trainingId";
        } else {
            $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, TP.TrainingProviderlogo, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId GROUP BY T.trainingId";
        }
        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, TP.TrainingProviderlogo, T.trainingName, T.offerMode, T.trainingDescription, T.startDate, T.endDate, T.status, ifnull((SELECT COUNT(TN.traineesId) FROM trainees TN WHERE TN.trainingId
      = T.trainingId AND TN.status = 'Approved'),0) trainees FROM trainings T INNER JOIN trainingProviders TP ON T.trainingProviderId = TP.trainingProviderId WHERE T.trainingId = :trainingId LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':trainingId' => $training_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTrainingsOnSchool($school_code)
    {

        $statement = "SELECT  T.* FROM trainings T
        INNER JOIN cohorts C ON C.trainingId = T.trainingId
        INNER JOIN  cohortconditions CC ON CC.cohortId = C.cohortId
        WHERE CC.school_code = :school_code OR CC.sector_code = :sector_code OR CC.district_code = :district_code ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":school_code" => $school_code,
                ":sector_code" => substr($school_code, 0, 4),
                ":district_code" => substr($school_code, 0, 2),
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $statement = "INSERT INTO trainings (trainingName, trainingDescription, offerMode, trainingProviderId, startDate, endDate, training_type_id, createdBy)
      VALUES(:trainingName, :trainingDescription, :offerMode, :trainingProviderId, :startDate, :endDate, :training_type_id,:createdBy)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingName' => $data['trainingName'],
                ':trainingDescription' => $data['trainingDescription'],
                ':offerMode' => $data['offerMode'],
                ':trainingProviderId' => $data['trainingProviderId'],
                ':startDate' => $data['startDate'],
                ':endDate' => $data['endDate'],
                ':training_type_id' => $data['training_type_id'],
                ':createdBy' => $user_id,
            ));
            $data['trainingId'] = $this->db->lastInsertId();
            $data['TrainingProviderlogo'] = null;
            $data['trainees'] = '0';
            $data['status'] = 'Waiting';

            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTraningsByStatus($status)
    {
        $statement = "SELECT  T.trainingId, T.trainingProviderId, TP.trainingProviderName, TP.TrainingProviderlogo, T.trainingName, T.trainingDescription, T.startDate, T.endDate, T.status,
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
        $documents = "supporting_documents";
        $logo = "TrainingProviderlogo";
        $statement = "INSERT INTO `trainingProviders`(`trainingProviderName`, `description`, `email`, `address`, `phone_number`, `supporting_documents`, `TrainingProviderlogo`, `createdBy`) VALUES (:trainingProviderName, :description, :email, :address, :phone_number, :supporting_documents, :TrainingProviderlogo, :createdBy)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingProviderName' => $data->trainingProviderName,
                ':description' => $data->description,
                ':email' => $data->email,
                ':address' => $data->address,
                ':phone_number' => $data->phone_number,
                ':supporting_documents' => $data->$documents,
                ':TrainingProviderlogo' => $data->$logo,
                ':createdBy' => $doneBY,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function createNewTrainingProviderUser($data, $doneBY)
    {
        $statement = "INSERT INTO `user_to_trainingprovider`(`user_to_trainingprovider_id`, `user_id`, `training_provider_id`, `status`, `created_by`) VALUES (:user_to_trainingprovider_id, :user_id, :training_provider_id, :status, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_to_trainingprovider_id' => $data['user_to_trainingprovider_id'],
                ':user_id' => $data['user_id'],
                ':training_provider_id' => $data['training_provider_id'],
                ':status' => $data['status'],
                ':created_by' => $doneBY,
            ));

            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateTrainingProviderUser($data, $doneBY)
    {

        $statement = "UPDATE `user_to_trainingprovider` SET `user_id`= :user_id,`training_provider_id`= :training_provider_id,`status`= :status, `updated_by`= :updated_by WHERE `user_to_trainingprovider_id`= :user_to_trainingprovider_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_id' => $data['user_id'],
                ':training_provider_id' => $data['training_provider_id'],
                ':status' => $data['status'],
                ':updated_by' => $doneBY,
                ':user_to_trainingprovider_id' => $data['user_to_trainingprovider_id'],
            ));

            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTrainingProviderUsers($training_provider_id)
    {

        $statement = "SELECT U.* FROM `user_to_trainingprovider` UTP
        INNER JOIN users U ON UTP.user_id = U.user_id
        WHERE UTP.`training_provider_id`= :training_provider_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':training_provider_id' => $training_provider_id,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $removeUnwantedKeys = function ($object) {
                unset($object["username"]);
                unset($object["password"]);
                return $object;
            };
            $resultsDetails = array_map($removeUnwantedKeys, $result);
            return $resultsDetails;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTrainingProviderUserDetails($user_id)
    {

        $statement = "SELECT TP.*, UTP.user_to_trainingprovider_id, UTP.user_id, UTP.status FROM `user_to_trainingprovider` UTP
        INNER JOIN trainingProviders TP ON TP.trainingProviderId = UTP.training_provider_id
        WHERE UTP.`user_id`= :user_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_id' => $user_id,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectOneTrainingProviderUser($user_to_trainingprovider_id)
    {
        $statement = "SELECT * FROM `user_to_trainingprovider`
        WHERE `user_to_trainingprovider_id`= :user_to_trainingprovider_id ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_to_trainingprovider_id' => $user_to_trainingprovider_id,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function ProviderExists($data)
    {
        $sql = "SELECT * FROM trainingProviders WHERE trainingProviderName = :trainingProviderName OR email = :email OR phone_number = :phone_number LIMIT 1";
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

    public function findOneTrainingProvider($trainingProviderId)
    {
        $sql = "SELECT * FROM trainingProviders WHERE trainingProviderId = :trainingProviderId LIMIT 1";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':trainingProviderId' => $trainingProviderId,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
