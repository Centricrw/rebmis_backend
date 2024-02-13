<?php
namespace Src\Models;

use Error;

class TraineersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTrainees($cohortId, $user_role_details)
    {
        $condition = "";
        $conditionArray = array(
            ':cohortId' => $cohortId,
            ':status' => "Active",
        );
        $role = $user_role_details['role_id'];

        if (isset($user_role_details['school_code']) && $role == "2") {
            $condition = "AND T.school_code = :school_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':school_code' => $user_role_details['school_code'],
            );
        } elseif (isset($user_role_details['sector_code']) && $role == "18") {
            $condition = "AND T.sector_code = :sector_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':sector_code' => $user_role_details['sector_code'],
            );
        } elseif (isset($user_role_details['district_code']) && ($role == "3" || $role == "7")) {
            $condition = "AND T.district_code = :district_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':district_code' => $user_role_details['district_code'],
            );
        } else {
            $condition = "";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
            );
        }

        $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name, UR.role_id, UR.qualification_id, UR.position_code, UR.status, U.staff_code, U.email, U.nid, U.sex FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            INNER JOIN user_to_role UR ON T.userId = UR.user_id
            INNER JOIN users U ON U.user_id = UR.user_id
            WHERE T.cohortId = :cohortId AND UR.status = :status $condition";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($conditionArray);
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $teachers;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getGenratedReportTraineesBySchool($cohortId, $school)
    {
        try {
            $statement = "SELECT GR.*, TR.trainingName, CH.cohortStart, CH.cohortEnd FROM `general_report` GR
            INNER JOIN trainings TR ON TR.trainingId = GR.trainingId
            INNER JOIN cohorts CH ON CH.cohortId = GR.cohortId
            WHERE GR.`cohortId` = :cohortId AND GR.`school_code` = :school_code";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(":cohortId" => $cohortId, ":school_code" => $school));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function getGenratedReportTraineesByUser($userId, $cohortId)
    {
        try {
            $statement = "SELECT GR.*, TR.trainingName, CH.cohortStart, CH.cohortEnd FROM `general_report` GR
            INNER JOIN trainings TR ON TR.trainingId = GR.trainingId
            INNER JOIN cohorts CH ON CH.cohortId = GR.cohortId
            WHERE GR.`userId` = :userId AND GR.`cohortId` = :cohortId";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(":userId" => $userId, ":cohortId" => $cohortId));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function getGenratedReportTraineesByStaff($staffCode, $cohortId)
    {
        try {
            $statement = "SELECT GR.*, TR.trainingName, CH.cohortStart, CH.cohortEnd FROM `general_report` GR
            INNER JOIN trainings TR ON TR.trainingId = GR.trainingId
            INNER JOIN cohorts CH ON CH.cohortId = GR.cohortId
            WHERE GR.`staff_code` = :staff_code AND GR.`cohortId` = :cohortId";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(":staff_code" => $staffCode, ":cohortId" => $cohortId));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function getGenratedReportTrainees($cohortId)
    {
        try {
            $statement = "SELECT GR.*, TR.trainingName, CH.cohortStart, CH.cohortEnd FROM `general_report` GR
            INNER JOIN trainings TR ON TR.trainingId = GR.trainingId
            INNER JOIN cohorts CH ON CH.cohortId = GR.cohortId
            WHERE GR.`cohortId` = :cohortId";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(":cohortId" => $cohortId));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function countTrainees($trainingId, $location_code = "", $userType = "")
    {
        try {
            switch ($userType) {
                case 'School':
                    $statement = "SELECT COUNT(traineesId) AS numberOfTrainees FROM trainees WHERE school_code = :school_code AND trainingId = :trainingId";
                    $statement = $this->db->prepare($statement);
                    $statement->execute(array(":trainingId" => $trainingId, ":school_code" => $location_code));
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    return $result;
                case 'Sector':
                    $statement = "SELECT COUNT(traineesId) AS numberOfTrainees  FROM trainees WHERE sector_code = :sector_code AND trainingId = :trainingId";
                    $statement = $this->db->prepare($statement);
                    $statement->execute(array(":trainingId" => $trainingId, ":sector_code" => $location_code));
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    return $result;
                case 'District':
                    $statement = "SELECT COUNT(traineesId) AS numberOfTrainees  FROM trainees WHERE district_code = :district_code AND trainingId = :trainingId";
                    $statement = $this->db->prepare($statement);
                    $statement->execute(array(":trainingId" => $trainingId, ":district_code" => $location_code));
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    return $result;
                default:
                    $statement = "SELECT COUNT(traineesId) AS numberOfTrainees  FROM trainees WHERE trainingId = :trainingId";
                    $statement = $this->db->prepare($statement);
                    $statement->execute(array(":trainingId" => $trainingId));
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    return $result;
            }
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

}
