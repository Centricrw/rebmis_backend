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


    public function getCandidates($schools)
    {
        $newSchools = [];
        foreach ($schools as $school) {
            $newSchool = [];
            $newSchool['schoolCode']=$school;
            $newSchool['teachers'] = $this->getSchoolTeachers($school); 
            array_push($newSchools, $newSchool);
        }
        return $newSchools;
    }

    public function addFocalTeacher($data)
    {
        $schoolCode = $data['schoolCode'];
        $teacherCode = $data['staff_code'];
        $statement = "UPDATE user_to_role SET custom_role = ? WHERE user_id = ? AND school_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('FOCAL_TEACHER',$teacherCode,$schoolCode));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getSchoolTeachers($schoolCode)
    {
        $statement = 'SELECT U.staff_code, U.full_name FROM user_to_role UR INNER JOIN users U ON U.user_id = UR.user_id WHERE UR.school_code = ? AND UR.role_id = ?';
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
