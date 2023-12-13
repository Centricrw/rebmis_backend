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
}
