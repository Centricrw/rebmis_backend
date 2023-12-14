<?php
namespace Src\Models;

use Error;

class CopReportsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createNewCopReport($data)
    {
        $statement = "INSERT INTO `cop_report`(`cop_report_id`, `cohortId`, `cop_report_title`, `created_by`)
      VALUES(:cop_report_id, :cohortId, :cop_report_title, :created_by)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cop_report_id' => $data['cop_report_id'],
                ':cohortId' => $data['cohortId'],
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
        $statement = "SELECT * FROM `cop_report` WHERE `cop_report_title` = :cop_report_title AND `cohortId` = :cohortId LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortId' => $data['cohortId'],
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
        $statement = "SELECT * FROM `cop_report`";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllCopReportsByCohortId($cohortId)
    {
        $statement = "SELECT * FROM `cop_report` where `cohortId` = :cohortId ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortId' => $cohortId,
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
            return $data;
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

    public function createNewCopReportDetailsReports($data)
    {
        $statement = "INSERT INTO `cop_report_details_report`(`report_id`, `cop_report_details_id`, `district_code`, `sector_code`, `school_code`, `district_name`, `sector_name`, `school_name`, `meeting_date`, `course`, `course_summary`, `meeting_benefits`, `meeting_drawback`, `meeting_strategy`, `drawback_to_submit_at_school`, `next_meeting_date`, `next_meeting_superviser`, `meeting_attendance`, `meeting_supervisor`, `meeting_supervisor_occupation`, `created_by`)
      VALUES (:report_id, :cop_report_details_id, :district_code, :sector_code, :school_code, :district_name, :sector_name, :school_name, :meeting_date, :course, :course_summary, :meeting_benefits, :meeting_drawback, :meeting_strategy, :drawback_to_submit_at_school, :next_meeting_date, :next_meeting_superviser, :meeting_attendance, :meeting_supervisor, :meeting_supervisor_occupation, :created_by)";

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
                ':drawback_to_submit_at_school' => $data['drawback_to_submit_at_school'],
                ':next_meeting_date' => isset($data['next_meeting_date']) ? $data['next_meeting_date'] : null,
                ':next_meeting_superviser' => isset($data['next_meeting_superviser']) ? $data['next_meeting_superviser'] : null,
                ':meeting_attendance' => json_encode($data['meeting_attendance'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ':meeting_supervisor' => $data['meeting_supervisor'],
                ':meeting_supervisor_occupation' => $data['meeting_supervisor_occupation'],
                ':created_by' => $data['created_by'],
            ));
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
