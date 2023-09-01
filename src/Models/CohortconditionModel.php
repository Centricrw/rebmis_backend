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

    public function createCondition($data, $user_id, $cohortId)
    {
        $statement = "INSERT INTO cohortconditions (schoolLocation, availabletrainees, capacity, cohortId, createdBy)
      VALUES(:schoolLocation,:availabletrainees, :capacity, :cohortId, :createdBy)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':schoolLocation' => serialize($data['location']), // implode($data['location']),
                ':availabletrainees' => $data['availabletrainees'],
                ':capacity' => $data['limit'],
                ':cohortId' => $cohortId,
                ':createdBy' => $user_id,
            ));
            $conditionId = $this->db->lastInsertId();
            $data['cohortConditionId'] = $conditionId;
            $data['providedTrainees'] = '0';
            $this->listAvailableTrainees($data['conditions'], $data['trainingId'], $cohortId, $conditionId);
            return $data;
        } catch (\PDOException $e) {
            exit($e->getMessage());
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
        $statement = "SELECT cohortConditionId, schoolLocation, availabletrainees, capacity, IFNULL((SELECT COUNT(T.traineesId) FROM trainees T WHERE T.cohortId = CC.cohortId AND status = 'Approved'),0) providedTrainees FROM cohortconditions CC WHERE CC.cohortId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohortId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $finalres = [];
            foreach ($results as $result) {
                $condition = new \stdClass();
                $condition->cohortConditionId = $result['cohortConditionId'];
                $condition->location = unserialize($result['schoolLocation']);
                $condition->availabletrainees = $result['availabletrainees'];
                $condition->limit = $result['capacity'];
                $condition->providedTrainees = $result['providedTrainees'];
                array_push($finalres, $condition);
            };
            return $finalres;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function listAvailableTrainees($conditions, $trainingId, $cohortId, $conditionId)
    {
        $location = explode("/", $conditions)[0];
        $route = explode("/", $conditions)[1];
        $id = explode("/", $conditions)[2];
        return $trainees = $this->$route($id, $trainingId, $cohortId, $conditionId);

        //explode("/",$conditions)[0];
    }

    private function getperschool($schoolcode, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
      SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
          INNER JOIN users U ON U.user_id = UR.user_id
              WHERE UR.school_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($schoolcode));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getvillages($villagecode, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
      SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
              INNER JOIN users U ON U.user_id = UR.user_id
              LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
              WHERE SCF.village_id = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($villagecode));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getcells($cellcode, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
      SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
              INNER JOIN users U ON U.user_id = UR.user_id
              LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
              WHERE SCF.cell_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cellcode));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getsectors($sectorcode, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
      SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
              INNER JOIN users U ON U.user_id = UR.user_id
              LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
              WHERE SCF.sector_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($sectorcode));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getdistricts($provinceId, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
              SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
              INNER JOIN users U ON U.user_id = UR.user_id
              LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
              WHERE SCF.district_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($provinceId));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function getprovince($provinceId, $trainingId, $cohortId, $conditionId)
    {
        $statement = "INSERT INTO trainees(userId, traineeName, traineePhone, trainingId, cohortId, conditionId)
              SELECT UR.user_id, U.full_name, U.phone_numbers,$trainingId,$cohortId,$conditionId FROM user_to_role UR
              INNER JOIN users U ON U.user_id = UR.user_id
              LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
              WHERE SCF.province_code = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($provinceId));
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $this->getTrainees($conditionId);
        } catch (\PDOException $e) {
            exit($e->getMessage());
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
        $statement = "SELECT TCH.teacher_study_hierarchy_id,U.full_name, U.staff_code, SH.combination_name, SH.grade_name, SH.course_name, TCH.status, S.school_name, S.school_code, UR.sector_code, UR.district_code FROM user_to_role UR
        INNER JOIN users U ON  UR.user_id = U.user_id
        INNER JOIN schools S ON S.school_code = UR.school_code
        INNER JOIN teacher_study_hierarchy TCH ON TCH.teacher_code = U.staff_code
        INNER JOIN study_hierarchy SH ON SH.studyhierarchyid = TCH.study_hierarchy_id
        WHERE S.school_code LIKE '$likeSchoolcode%' AND TCH.status = 1
        AND UR.status = 'Active' $sqlConditionString LIMIT " . $condition['limit'];
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
