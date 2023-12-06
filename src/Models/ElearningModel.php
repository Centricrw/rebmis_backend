<?php
namespace Src\Models;

use Error;

class ElearningModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function connectCourse($cohortId, $courseLink)
    {
        $statement = "UPDATE cohorts SET courseLink = :courseLink WHERE cohortId = :cohortId";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortId' => $cohortId,
                ':courseLink' => $courseLink,
            ));
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function linkUserToCourse($course_id, $staff_code)
    {
        $statement = "UPDATE trainees SET course_id = :course_id WHERE userId = (SELECT user_id FROM users WHERE staff_code =:staff_code)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':course_id' => $course_id,
                ':staff_code' => $staff_code,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
