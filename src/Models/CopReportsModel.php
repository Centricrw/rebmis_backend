<?php
namespace Src\Models;

use Error;
use stdClass;

class CopReportsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createNewCopReport($data)
    {
        $statement = "INSERT INTO `cop_report`(`cop_report_id`, `trainingId`, `cop_report_title`, `created_by`)
      VALUES(:cop_report_id, :trainingId, :cop_report_title, :created_by)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_id' => $data['cop_report_id'],
                ':trainingId' => $data['trainingId'],
                ':cop_report_title' => $data['cop_report_title'],
                ':created_by' => $data['created_by'],
            ));
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getCopReportsByID($copReportId)
    {
        $statement = "SELECT * FROM `cop_report` WHERE `cop_report_id` = :cop_report_id LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_id' => $copReportId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getCopReportsByTitle($data)
    {
        $statement = "SELECT * FROM `cop_report` WHERE `cop_report_title` = :cop_report_title AND `trainingId` = :trainingId LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingId' => $data['trainingId'],
                ':cop_report_title' => $data['cop_report_title'],
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllCopReports()
    {
        $statement = "SELECT * FROM `cop_report` ORDER BY listing ASC";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllCopReportsByTraining($trainingId)
    {
        $statement = "SELECT * FROM `cop_report` where `trainingId` = :trainingId ORDER BY listing ASC";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingId' => $trainingId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function createNewCopReportDetails($data)
    {
        $statement = "INSERT INTO `cop_report_details`(`cop_report_details_id`, `cop_report_id`, `cop_report_details_title`, `start_date`, `end_date`, `created_by`)
        VALUES(:cop_report_details_id, :cop_report_id, :cop_report_details_title, :start_date, :end_date, :created_by)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_details_id' => $data['cop_report_details_id'],
                ':cop_report_id' => $data['cop_report_id'],
                ':cop_report_details_title' => $data['cop_report_details_title'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':created_by' => $data['created_by'],
            ));

            // GENERATE A REPORT FOR THIS MODULE UNIT (REPORT DETAILS)
            $newData = $this->generateReport($data['cop_report_id'], $data['cop_report_details_id'], $data['cohortId'], $data['trainingId']);
            if($newData){
                return $data; 
            }else{
                return $data;
            }
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    private function updateGeneralReport($moduleId, $chapterId, $cohortId, $trainingId){
        //SELECT TRAININEES
        //LOOP TRAINEES
        //UPDATE GENERAL REPORT
    }
    private function generateReport($moduleId, $chapterId, $cohortId, $trainingId){

        $statement = "INSERT IGNORE INTO general_report (
        traineeId, traineeName, traineePhone, staff_code,
        district_code, sector_code, school_code, 
        district_name, sector_name, school_name, 
        age, gender, disability,
        moduleId, chapterId, moduleName, chapterName,
        cohortId, trainingId, userId)
        SELECT 
        TR.traineesId, TR.traineeName, TR.traineePhone, (SELECT staff_code FROM users U WHERE U.user_id = TR.userId) staff_code,
        TR.district_code, TR.sector_code, TR.school_code,
        (SELECT DISTINCT(district_name) FROM school_location WHERE district_code = TR.district_code LIMIT 1) district_name,
        (SELECT DISTINCT(sector_name) FROM school_location WHERE sector_code = TR.sector_code LIMIT 1) sector_name,
        (SELECT DISTINCT(school_name) FROM schools WHERE school_code = TR.school_code) school_name,
        (SELECT DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), U.dob)), '%Y') + 0 AS age FROM users U WHERE U.user_id = TR.userId) age,
        (SELECT sex FROM users U WHERE U.user_id = TR.userId) gender,
        (SELECT disability FROM users U WHERE U.user_id = TR.userId) disability,
        '".$moduleId."', '".$chapterId."', 
        (SELECT cop_report_title FROM cop_report WHERE cop_report_id = '".$moduleId."') moduleName,
        (SELECT cop_report_details_title FROM cop_report_details WHERE cop_report_details_id = '".$chapterId."') chapterName,
        '".$cohortId."', '".$trainingId."', TR.userId
        FROM trainees TR
        WHERE TR.cohortId = '".$cohortId."'";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getCopReportsDetailsByID($copReportId)
    {
        $statement = "SELECT * FROM `cop_report_details` WHERE `cop_report_details_id` = :cop_report_details_id LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_details_id' => $copReportId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllCopReportsDetailsByCopReportId($copReportId)
    {
        $statement = "SELECT * FROM `cop_report_details` WHERE `cop_report_id` = :cop_report_id ORDER BY listing ASC";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_id' => $copReportId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getCopReportsDetailsByTitle($data)
    {
        $statement = "SELECT * FROM `cop_report_details` WHERE `cop_report_id` = :cop_report_id AND `cop_report_details_title` = :cop_report_details_title LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_id' => $data['cop_report_id'],
                ':cop_report_details_title' => $data['cop_report_details_title'],
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function createNewCopReportDetailsReports($data)
    {
        $statement = "INSERT INTO `cop_report_details_report`(`report_id`, `cop_report_details_id`, `district_code`, `sector_code`, `school_code`, `district_name`, `sector_name`, `school_name`, `meeting_date`, `course`, `course_summary`, `meeting_benefits`, `meeting_drawback`, `meeting_strategy`, `drawback_to_submit_at_school`, `next_meeting_date`, `next_meeting_superviser`, `meeting_attendance`, `meeting_supervisor`, `meeting_supervisor_occupation`, `cohortsId`, `created_by`)
      VALUES (:report_id, :cop_report_details_id, :district_code, :sector_code, :school_code, :district_name, :sector_name, :school_name, :meeting_date, :course, :course_summary, :meeting_benefits, :meeting_drawback, :meeting_strategy, :drawback_to_submit_at_school, :next_meeting_date, :next_meeting_superviser, :meeting_attendance, :meeting_supervisor, :meeting_supervisor_occupation, :cohortsId, :created_by)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':report_id' => $data['report_id'],
                ':cop_report_details_id' => $data['cop_report_details_id'],
                ':district_code' => $data['district_code'],
                ':sector_code' => $data['sector_code'],
                ':school_code' => $data['school_code'],
                ':district_name' => $data['district_name'],
                ':sector_name' => $data['sector_name'],
                ':school_name' => $data['school_name'],
                ':meeting_date' => $data['meeting_date'],
                ':course' => $data['course'],
                ':course_summary' => $data['course_summary'],
                ':meeting_benefits' => $data['meeting_benefits'],
                ':meeting_drawback' => $data['meeting_drawback'],
                ':meeting_strategy' => $data['meeting_strategy'],
                ':cohortsId' => $data['cohortsId'],
                ':drawback_to_submit_at_school' => $data['drawback_to_submit_at_school'],
                ':next_meeting_date' => isset($data['next_meeting_date']) ? $data['next_meeting_date'] : null,
                ':next_meeting_superviser' => isset($data['next_meeting_superviser']) ? $data['next_meeting_superviser'] : null,
                ':meeting_attendance' => json_encode($data['meeting_attendance'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ':meeting_supervisor' => $data['meeting_supervisor'],
                ':meeting_supervisor_occupation' => $data['meeting_supervisor_occupation'],
                ':created_by' => $data['created_by'],
            ));
            // THIS WAS USED TO MARK A TRAINEE WHO ATTENDED THE COP MEETING BUT IT IS NO LONGER NEEDED FOR NOW
            /*$chapterId = $data['cop_report_details_id'];
            $keepNothing = 0;
            foreach ($data['meeting_attendance'] as $teacher) {
                if($this->markAttendenceOfCopOnTheReport($teacher['traineesId'], $chapterId, $data['cohortsId'])){
                    $keepNothing++;
                }
            }*/
            // WE KEEP IT TO BE USED LATER
           
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    private function markAttendenceOfCopOnTheReport($traineesId, $chapterId, $cohortId)
    {
        //UPDATE general
        $updatedQuery = "UPDATE general_report SET copMarks= :copMarks WHERE traineeId = :traineeId AND chapterId = :chapterId AND cohortId = :cohortId";
        try {
            $statement = $this->db->prepare($updatedQuery);
            $statement->execute(array(
                ':copMarks'     => '100',
                ':traineeId'    => $traineesId,
                ':chapterId'       => $chapterId,
                ':cohortId'     => $cohortId
            ));
            $result = $statement->rowCount();
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getCopReportsDetailsReportBySchool($data)
    {
        $statement = "SELECT * FROM `cop_report_details_report` WHERE `cop_report_details_id` = :cop_report_details_id AND `school_code` = :school_code LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_details_id' => $data['cop_report_details_id'],
                ':school_code' => $data['school_code'],
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllReportsBYCopReportDetailsId($copReportDetailsId)
    {
        $statement = "SELECT * FROM `cop_report_details_report` WHERE `cop_report_details_id` = :cop_report_details_id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_details_id' => $copReportDetailsId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllReportsBYUser($user_id, $copReportDetailsId)
    {
        $statement = "SELECT * FROM `cop_report_details_report` WHERE `created_by` = :created_by AND `cop_report_details_id` = :cop_report_details_id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':created_by' => $user_id,
                ':cop_report_details_id' => $copReportDetailsId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
