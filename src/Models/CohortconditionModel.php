<?php
namespace Src\Models;

use Error;

class CohortconditionModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // {
    //     "": "1",
    //     "": "11",
    //     "": "",
    //     "": "",
    //     "": "",
    //     "": "",
    //     "": "English",
    //     "": 0,
    //     "": 1000,
    //     "cohort_id": "",
    //     "": ""
    // }

    public function createCondition($data, $user_id)
    {
        $statement = "INSERT INTO cohortconditions (cohortconditionId, capacity, cohortId, createdBy, provincecode, district_code, sector_code, school_code, combination_code, grade_code, course_name, comfirmed, approval_role_id)
      VALUES(:cohortconditionId, :capacity, :cohortId, :createdBy, :provincecode, :district_code, :sector_code, :school_code, :combination_code, :grade_code, :course_name, :comfirmed, :approval_role_id)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortconditionId' => $data['cohortconditionId'],
                ':capacity' => $data['capacity'],
                ':cohortId' => $data['cohortId'],
                ':provincecode' => $data['provincecode'],
                ':district_code' => $data['district_code'],
                ':sector_code' => empty($data['sector_code']) ? null : $data['sector_code'],
                ':school_code' => empty($data['school_code']) ? null : $data['school_code'],
                ':combination_code' => empty($data['combination_code']) ? null : $data['combination_code'],
                ':grade_code' => empty($data['grade_code']) ? null : $data['grade_code'],
                ':course_name' => empty($data['course_name']) ? null : $data['course_name'],
                ':comfirmed' => $data['comfirmed'],
                ':approval_role_id' => $data['approval_role_id'],
                ':createdBy' => $user_id,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function approveselected($traineesId, $user_id, $cohorConditiontId)
    {
        $statement = "UPDATE trainees SET `status` = 'Approved' WHERE userId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($traineesId));
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function cleanrejected($cohorConditiontId)
    {
        $statement = "DELETE FROM trainees WHERE status <> :status AND conditionId = :";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':status' => 'Approved', ':conditionId' => $cohorConditiontId));
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getAllConditions($cohortId)
    {
        $statement = "SELECT *, IFNULL((SELECT COUNT(T.traineesId) FROM trainees T WHERE T.cohortId = CC.cohortId AND status = 'Approved'),0) providedTrainees FROM cohortconditions CC WHERE CC.cohortId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohortId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTrainees($conditionId)
    {
        $statement = "SELECT * FROM trainees TR
              WHERE TR.cohortId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($conditionId));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $teachers;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getSchoolsByLocation($location)
    {
        $stringArray = ["provincecode", "district_code", "sector_code", "school_code"];
        $likeSchoolcode = 0;
        // splitig condition into sql condition
        foreach ($stringArray as $key => $value) {
            if (isset($location[$value]) && $location[$value] != "") {
                $likeSchoolcode = $value == "provincecode" ? $location[$value] : (
                    $value == "district_code" ? $location[$value] : (
                        $value == "sector_code" ? $location[$value] : (
                            $value == "school_code" ? $location[$value] : 0
                        )
                    )
                );
            }
        };
        try {
            $statement = "SELECT * FROM `schools` WHERE `school_code` LIKE '$likeSchoolcode%'";
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $schools = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $schools;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function GetStudyHierarchy()
    {
        try {
            $query = "SELECT * FROM study_hierarchy";
            $statement = $this->db->prepare($query);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTeacherByConditions($condition)
    {
        $stringArray = ["provincecode", "district_code", "sector_code", "school_code", "combination_code", "grade_code", "course_name"];
        $sqlConditionArray = array(
            "combination_code" => "SH.combination_code = :combination_code",
            "grade_code" => "SH.grade_code = :grade_code",
            "course_code" => "SH.course_code = :course_code",
            "course_name" => "SH.course_name = :course_name",
        );
        $sqlConditionString = "";
        $sqlConditionArrayValues = array();
        $likeSchoolcode = "0";
        // splitig condition into sql condition
        foreach ($stringArray as $key => $value) {
            if (isset($condition[$value]) && $condition[$value] != "") {
                if ($value == "provincecode" || $value == "district_code" || $value == "sector_code" || $value == "school_code") {
                    $likeSchoolcode = $value == "provincecode" ? $condition[$value] : (
                        $value == "district_code" ? $condition[$value] : (
                            $value == "sector_code" ? $condition[$value] : (
                                $value == "school_code" ? $condition[$value] : 0
                            )
                        )
                    );
                }
                if ($value != "provincecode" && $value != "district_code" && $value != "sector_code" && $value != "school_code") {
                    $sqlConditionString = $sqlConditionString . " AND " . $sqlConditionArray[$value];
                    $sqlConditionArrayValues[":$value"] = $condition[$value];
                }
            }
        };
        $statement = "SELECT DISTINCT TCH.teacher_code,U.full_name, U.staff_code, SH.combination_name, SH.grade_name, SH.course_name, TCH.status, S.school_name, S.school_code, UR.sector_code, UR.district_code FROM user_to_role UR
        INNER JOIN users U ON  UR.user_id = U.user_id
        INNER JOIN schools S ON S.school_code = UR.school_code
        INNER JOIN teacher_study_hierarchy TCH ON TCH.teacher_code = U.staff_code
        INNER JOIN study_hierarchy SH ON SH.studyhierarchyid = TCH.study_hierarchy_id
        WHERE S.school_code LIKE '$likeSchoolcode%' AND TCH.status = 1
        AND UR.status = 'Active' $sqlConditionString LIMIT " . $condition['capacity'];
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($sqlConditionArrayValues);
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $teachers;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
