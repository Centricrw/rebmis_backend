<?php
namespace Src\Models;

use Error;

class ReportModel
{

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

    public function getGeneralReportPerTraining($trainingId)
    {
        $statement = "SELECT * FROM general_report WHERE trainingId = $trainingId";
        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getGeneralReportPerTrainee($staff_code)
    {
        $statement = "SELECT * FROM general_report WHERE staff_code = '".$staff_code."'";
        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getGeneralReportPerTrainingForSchool($trainingId, $schoolCode)
    {
        $statement = "SELECT * FROM `general_report` WHERE `trainingId` = :trainingId AND `school_code` = :school_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingId' => $trainingId,
                ':school_code' => $schoolCode,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
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

    public function headTeacherTraineeMarking($data)
    {
        $markType = $data['markType'];
        $statement = "UPDATE `general_report` SET `htNotesMarks` = :htNotesMarks, `htClassMarks` = :htClassMarks WHERE userId = :userId AND unitId = :unitId AND cohortId = :cohortId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":userId" => $data['userId'],
                ":unitId" => $data['unitId'],
                ":cohortId" => $data['cohortId'],
                ":htNotesMarks" => $data['htNotesMarks'],
                ":htClassMarks" => $data['htClassMarks'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
