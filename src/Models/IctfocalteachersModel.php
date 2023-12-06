<?php
namespace Src\Models;

class IctfocalteachersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCandidates($data)
    {
        $cohort_id = $data['cohort_id'];
        $schools = $data['schools'];
        $newSchools = [];
        foreach ($schools as $school) {
            $newSchool = [];
            $newSchool['schoolCode'] = $school;
            $newSchool['teachers'] = $this->getSchoolTeachers($school, $cohort_id);
            array_push($newSchools, $newSchool);
        }
        //print_r($newSchools);
        return $newSchools;
    }

    public function removeFocalTeacher($data)
    {
        $schoolCode = $data['school_code'];
        $teacherCode = $data['staff_code'];
        $cohort_id = $data['cohort_id'];
        $statement = "DELETE FROM user_to_role_custom WHERE cohort_id= :cohort_id AND school_code= :schoolCode AND custom_role= 'FOCAL_TEACHER'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':schoolCode' => $schoolCode,
                ':cohort_id' => $cohort_id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function addFocalTeacher($data)
    {
        $this->removeFocalTeacher($data);
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
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function checkFocalTeacher($data)
    {
        $schoolCode = $data['school_code'];
        $teacherCode = $data['staff_code'];
        $cohort_id = $data['cohort_id'];
        $statement = "SELECT custom_role FROM user_to_role_custom WHERE cohort_id = ? AND school_code = ? AND staff_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohort_id, $schoolCode, $teacherCode));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function resetTeacherPwd($data)
    {
        // create a new cURL resource
        $ch = curl_init();
        $password = $data["new_password"];
        $staff_code = $data["teacher_staff_code"];
        // set URL and other appropriate options ?username='$data["teacher_staff_code"]'&password='$data["new_password"]'
        curl_setopt($ch, CURLOPT_URL, "https://elearning.reb.rw/sandbox/local/custom_service/resetpwd.php?username='.$staff_code.'&password='.$password.'');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // grab URL and pass it to the browser
        curl_exec($ch);

        // close cURL resource, and free up system resources
        curl_close($ch);
    }

    private function getSchoolTeachers($schoolCode, $cohortId)
    {
        $statement = 'SELECT U.staff_code, U.full_name, IFNULL((SELECT custom_role FROM user_to_role_custom WHERE cohort_id = ' . $cohortId . ' AND school_code = ' . $schoolCode . ' AND staff_code = U.staff_code LIMIT 1),NULL) custom_roles FROM user_to_role UR INNER JOIN users U ON U.user_id = UR.user_id WHERE UR.school_code = ? AND UR.role_id = ?';
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
