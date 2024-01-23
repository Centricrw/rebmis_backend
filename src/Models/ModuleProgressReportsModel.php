<?php
namespace Src\Models;

use Error;

class ModuleProgressReportsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new module progress reports
     * @param OBJECT $data
     * @return VOID
     */
    public function insertNewModuleProgressReports($data)
    {
        $statement = "INSERT INTO `module_progress_reports`(`module_progress_reports_id`, `staff_code`, `first_name`, `last_name`, `gender`, `age`, `disability`, `cohort`, `district`, `sector`, `school`, `module`) VALUES (:module_progress_reports_id, :staff_code, :first_name, :last_name, :gender, :age, :disability, :cohort, :district, :sector, :school, :module)";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":module_progress_reports_id" => $data['module_progress_reports_id'],
                ":staff_code" => $data['staff_code'],
                ":first_name" => isset($data['first_name']) ? $data['first_name'] : null,
                ":last_name" => isset($data['last_name']) ? $data['last_name'] : null,
                ":gender" => isset($data['gender']) ? $data['gender'] : null,
                ":age" => isset($data['age']) ? $data['age'] : null,
                ":disability" => isset($data['disability']) ? $data['disability'] : null,
                ":cohort" => isset($data['cohort']) ? $data['cohort'] : null,
                ":district" => isset($data['district']) ? $data['district'] : null,
                ":sector" => isset($data['sector']) ? $data['sector'] : null,
                ":school" => isset($data['school']) ? $data['school'] : null,
                ":module" => isset($data['module']) ? $data['module'] : null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectAllModuleProgressReports()
    {
        $statement = "SELECT * FROM `module_progress_reports`";
        try {
            $statement = $this->db->query($statement);
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectModuleProgressReportsById($moduleProgressReportsId)
    {
        $statement = "SELECT * FROM `module_progress_reports` WHERE module_progress_reports_id = :module_progress_reports_id LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":module_progress_reports_id" => $moduleProgressReportsId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectModuleProgressReportsByStaffCode($staffCode)
    {
        $statement = "SELECT * FROM `module_progress_reports` WHERE staff_code = :staff_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":staff_code" => $staffCode));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
