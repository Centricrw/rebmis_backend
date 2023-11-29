<?php
namespace Src\Models;

use Error;

class TeacherStudyHierarchy
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function selectStudyHierarchy()
    {
        $statement = "SELECT * FROM `study_hierarchy`";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
    // 22 Kinyarwanda

    public function insertNewTeacherStudyHierarchy($data)
    {
        $statement = "INSERT INTO `teacher_study_hierarchy`(`teacher_code`, `study_hierarchy_id`, `grade_group`) VALUES (:teacher_code, :study_hierarchy_id, :grade_group )";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':teacher_code' => $data['staff_code'],
                ':study_hierarchy_id' => $data['study_hierarchy_id'],
                ':grade_group' => isset($data['grade_group']) ? $data['grade_group'] : null,
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findTeacherStudyHierarchy($data)
    {
        $statement = "SELECT `teacher_study_hierarchy_id` FROM `teacher_study_hierarchy` WHERE `teacher_code`=:teacher_code AND `study_hierarchy_id`=:study_hierarchy_id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':teacher_code' => $data['staff_code'],
                ':study_hierarchy_id' => $data['study_hierarchy_id'],
            ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function disableTeacherStudyHierarchy($data)
    {
        $statement = "UPDATE `teacher_study_hierarchy` SET `status`=:status WHERE `teacher_code`=:teacher_code";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':teacher_code' => $data['staff_code'],
                ':status' => 0,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
