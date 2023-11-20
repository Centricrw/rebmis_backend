<?php
namespace Src\Models;

use Error;

class IctfocalteachersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function getCandidates($data)
    {
        $cohort_id = $data['cohortId'];
        $schools = $data['schools'];
        $newSchools = [];
        foreach ($schools as $school) {
            $newSchool = [];
            $newSchool['schoolCode']=$school;
            $newSchool['teachers'] = $this->getSchoolTeachers($school, $cohort_id); 
            array_push($newSchools, $newSchool);
        }
        return $newSchools;
    }

    public function addFocalTeacher($data)
    {
        $schoolCode = $data['school_code'];
        $teacherCode = $data['staff_code'];
        $cohort_id = $data['cohort_id'];
        $statement = "INSERT INTO user_to_role_custom (cohort_id, school_code, staff_code, custom_role) VALUES (:cohort_id, :schoolCode, :teacherCode, 'FOCAL_TEACHER')";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':schoolCode' => $schoolCode,
                ':teacherCode' => $teacherCode,
                ':cohort_id' => $cohort_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $data;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getSchoolTeachers($schoolCode, $cohortId)
    {
        $statement = 'SELECT U.staff_code, U.full_name, UR.custom_roles FROM user_to_role UR INNER JOIN users U ON U.user_id = UR.user_id WHERE UR.school_code = ? AND UR.role_id = ?';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($schoolCode, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
