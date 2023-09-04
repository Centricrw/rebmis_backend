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

    public function createCondition($data, $user_id)
    {
        $statement = "INSERT INTO cohortconditions (cohortconditionId, capacity, cohortId, createdBy, provincecode, district_code, sector_code, school_code, combination_code, grade_code, course_name, comfirmed, approval_role_id, district_name, sector_name, school_name, combination_name, grade_name)
      VALUES(:cohortconditionId, :capacity, :cohortId, :createdBy, :provincecode, :district_code, :sector_code, :school_code, :combination_code, :grade_code, :course_name, :comfirmed, :approval_role_id, :district_name, :sector_name, :school_name, :combination_name, :grade_name)";

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
                ':district_name' => empty($data['district_name']) ? null : $data['district_name'],
                ':sector_name' => empty($data['sector_name']) ? null : $data['sector_name'],
                ':school_name' => empty($data['school_name']) ? null : $data['school_name'],
                ':combination_name' => empty($data['combination_name']) ? null : $data['combination_name'],
                ':grade_name' => empty($data['grade_name']) ? null : $data['grade_name'],
                ':comfirmed' => $data['comfirmed'],
                ':approval_role_id' => $data['approval_role_id'],
                ':createdBy' => $user_id,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function InsertApprovedSelectedTraineers($data, $logged_user_id)
    {
        $statement = "INSERT INTO `trainees`(`traineesId`, `userId`, `trainingId`, `cohortId`, `conditionId`, `status`, `traineeName`, `traineePhone`, `district_code`, `sector_code`, `school_code`) VALUES (:traineesId, :userId, :trainingId, :cohortId, :conditionId, :status, :traineeName, :traineePhone, :district_code, :sector_code, :school_code)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":traineesId" => $data['traineesId'],
                ":userId" => $data['user_id'],
                ":trainingId" => $data['trainingId'],
                ":cohortId" => $data['cohortId'],
                ":conditionId" => $data['cohortconditionId'],
                ":status" => "Approved",
                ":traineeName" => $data['full_name'],
                ":traineePhone" => $data['traineePhone'],
                ":district_code" => substr($data['school_code'], 0, 2),
                ":sector_code" => substr($data['school_code'], 0, 4),
                ":school_code" => $data['school_code'],
            ));
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function checkIfTraineerAvailable($trainingId, $userId)
    {
        $statement = "SELECT * FROM trainees WHERE trainingId = :trainingId AND userId = :userId ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingId' => $trainingId,
                ':userId' => $userId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllConditions($cohortId, $userDistrictCode = "")
    {
        if (isset($userDistrictCode) && $userDistrictCode !== "") {
            $statement = "SELECT *, IFNULL((SELECT COUNT(T.traineesId) FROM trainees T WHERE T.cohortId = CC.cohortId AND status = 'Approved'),0) providedTrainees FROM cohortconditions CC WHERE CC.cohortId = ? AND CC.district_code = $userDistrictCode";
        } else {
            $statement = "SELECT *, IFNULL((SELECT COUNT(T.traineesId) FROM trainees T WHERE T.cohortId = CC.cohortId AND status = 'Approved'),0) providedTrainees FROM cohortconditions CC WHERE CC.cohortId = ?";
        }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohortId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTrainees($conditionId, $userDistrictCode = "")
    {
        if (isset($userDistrictCode) && $userDistrictCode !== "") {
            $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            WHERE T.cohortId = ? AND T.district_code = $userDistrictCode";
        } else {
            $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            WHERE T.cohortId = ?";
        }
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

    // function my_array_unique($array, $keep_key_assoc = false)
    // {
    //     $duplicate_keys = array();
    //     $tmp = array();

    //     foreach ($array as $key => $val) {
    //         // convert objects to arrays, in_array() does not support objects
    //         if (is_object($val)) {
    //             $val = (array) $val;
    //         }

    //         if (!in_array($val['teacher_code'], $tmp)) {
    //             $tmp[] = $val['teacher_code'];
    //         } else {
    //             $duplicate_keys[] = $key;
    //         }

    //     }

    //     foreach ($duplicate_keys as $key) {
    //         unset($array[$key]);
    //     }

    //     return $keep_key_assoc ? $array : array_values($array);
    // }

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
        $limit = $condition['capacity'];
        $statement = "SELECT TCH.teacher_code, U.user_id,U.full_name, U.staff_code, U.phone_numbers, MIN(SH.combination_name) as combination_name, MIN(SH.grade_name) as grade_name, GROUP_CONCAT(SH.course_name) as course_name, TCH.status, MIN(S.school_name) as school_name, MIN(S.school_code) as school_code, MIN(UR.sector_code) as sector_code, MIN(UR.district_code) as district_code FROM user_to_role UR
        INNER JOIN users U ON  UR.user_id = U.user_id
        INNER JOIN schools S ON S.school_code = UR.school_code
        INNER JOIN teacher_study_hierarchy TCH ON TCH.teacher_code = U.staff_code
        INNER JOIN study_hierarchy SH ON SH.studyhierarchyid = TCH.study_hierarchy_id
        WHERE U.user_id NOT IN (select userId from trainees) AND S.school_code LIKE '$likeSchoolcode%' AND TCH.status = 1
        AND UR.status = 'Active' $sqlConditionString
        GROUP BY U.user_id LIMIT $limit";
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
