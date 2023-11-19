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
        $statement = "'UPDATE UR SET UR.custom_roles = 'FOCAL_TEACHER' FROM user_to_role UR INNER JOIN users U ON U.user_id = UR.user_id WHERE U.user_id = '57101122041' AND UR.school_code = '571011'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getSchoolTeachers($schoolCode)
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
