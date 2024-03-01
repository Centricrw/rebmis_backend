<?php
namespace Src\Models;

use DateTime;
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

    public function selectCohortConditionById($conditionId)
    {
        $statement = "SELECT * FROM cohortconditions WHERE cohortconditionId = :cohortconditionId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":cohortconditionId" => $conditionId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTraineeByPhoneNumber($cohortId, $phoneNumber)
    {
        $statement = "SELECT * FROM `trainees` WHERE `traineePhone` = :traineePhone AND `cohortId` = :cohortId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":traineePhone" => $phoneNumber,
                ":cohortId" => $cohortId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTraineeByUserIDAndCohortID($user_id, $cohort_id)
    {
        $statement = "SELECT * FROM `trainees` WHERE `userId` = :userId AND `cohortId` = :cohortId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":userId" => $user_id, ":cohortId" => $cohort_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTraineeOnThatDistrict($cohort_id, $district_code)
    {
        $statement = "SELECT * FROM `trainees` WHERE `district_code` = :district_code AND `cohortId` = :cohort_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":district_code" => $district_code, ":cohort_id" => $cohort_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    // checnged to to cohortId from trainingTypeId
    public function selectTraineersForBYtrainingType($cohortId)
    {
        // $statement = "SELECT TRN.traineesId, TRN.userId FROM `trainees` TRN
        // INNER JOIN trainings TR ON TRN.trainingId = TR.trainingId
        // INNER JOIN training_type TY ON TR.training_type_id = TY.training_type_id
        // WHERE TY.`training_type_id` = :training_type_id";

        $statement = "SELECT traineesId, userId FROM `trainees`
        WHERE `cohortId` = :cohortId";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":cohortId" => $cohortId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectTraineesOnThatSchools($training_id, $school_code)
    {
        $statement = "SELECT * FROM `trainees` WHERE `school_code` = :school_code AND `trainingId` = :trainingId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":school_code" => $school_code, ":trainingId" => $training_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function countTraineersOnCondition($data)
    {
        $statement = "SELECT userId FROM trainees WHERE conditionId = :conditionId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":conditionId" => $data['cohortconditionId'],
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getAllReportsAssignedToTraining($trainingId)
    {
        $COPstatement = "SELECT cop_report_id, trainingId, cop_report_title  FROM cop_report WHERE trainingId = :trainingId";

        $DatilsQuery = "SELECT cop_report_details_id, cop_report_details_title FROM cop_report_details WHERE cop_report_id = :cop_report_id";
        try {
            $COPstatement = $this->db->prepare($COPstatement);
            $COPstatement->execute(array(
                ':trainingId' => $trainingId,
            ));
            $COPresult = $COPstatement->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($COPresult as $key => $value) {
                $Dstatement = $this->db->prepare($DatilsQuery);
                $Dstatement->execute(array(
                    ':cop_report_id' => $value['cop_report_id'],
                ));
                $results = $Dstatement->fetchAll(\PDO::FETCH_ASSOC);
                $COPresult[$key]["details"] = $results;
            }
            return $COPresult;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    function traineeHasChapterHandler($data, $chapterId)
    {
        $query = "SELECT generalReportId FROM general_report WHERE userId = :userId AND cohortId = :cohortId AND chapterId = :chapterId";
        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                ':userId' => $data['user_id'],
                ':cohortId' => $data['cohortId'],
                ':chapterId' => $chapterId,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    function getTraineeInfo($user_id)
    {
        $query = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                ':user_id' => $user_id,
            ));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    function getTraineeSchoolLactionInfo($school_code)
    {
        $schoolQuery = "SELECT school_name FROM `schools` WHERE school_code = :school_code LIMIT 1";
        $districtQuery = "SELECT namedistrict FROM `districts` WHERE districtcode = :districtcode LIMIT 1";
        $sectorQuery = "SELECT namesector FROM `sectors` WHERE sectorcode = :sectorcode LIMIT 1";
        try {
            // getting school info
            $schoolStatement = $this->db->prepare($schoolQuery);
            $schoolStatement->execute(array(':school_code' => $school_code));
            $schoolInfo = $schoolStatement->fetchAll(\PDO::FETCH_ASSOC);

            // getting district info
            $districtStatement = $this->db->prepare($districtQuery);
            $districtStatement->execute(array(':districtcode' => substr($school_code, 0, 2)));
            $districtInfo = $districtStatement->fetchAll(\PDO::FETCH_ASSOC);

            // getting sector info
            $sectorStatement = $this->db->prepare($sectorQuery);
            $sectorStatement->execute(array(':sectorcode' => substr($school_code, 0, 4)));
            $sectorInfo = $sectorStatement->fetchAll(\PDO::FETCH_ASSOC);

            return array(
                "district_code" => substr($school_code, 0, 2),
                "district_name" => $districtInfo[0]["namedistrict"],
                "sector_code" => substr($school_code, 0, 4),
                "sector_name" => $sectorInfo[0]["namesector"],
                "school_code" => $school_code,
                "school_name" => $schoolInfo[0]["school_name"],
            );
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function insertTraineeToGeneralReport($data)
    {
        // get allowed chapters
        $statement = "INSERT INTO `general_report`(`traineeId`, `userId`, `traineeName`, `traineePhone`, `staff_code`, `cohortId`, `moduleId`, `moduleName`, `chapterId`, `chapterName`, `age`, `gender`, `disability`, `district_code`, `district_name`, `sector_code`, `sector_name`, `school_code`, `school_name`, `trainingId`) VALUES (:traineeId, :userId, :traineeName, :traineePhone, :staff_code, :cohortId, :moduleId, :moduleName, :chapterId, :chapterName, :age, :gender, :disability, :district_code, :district_name, :sector_code, :sector_name, :school_code, :school_name, :trainingId)
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":traineeId" => $data["traineeId"],
                ":userId" => $data["userId"],
                ":traineeName" => $data["traineeName"],
                ":traineePhone" => $data["traineePhone"],
                ":staff_code" => $data["staff_code"],
                ":cohortId" => $data["cohortId"],
                ":moduleId" => $data["moduleId"],
                ":moduleName" => $data["moduleName"],
                ":chapterId" => $data["chapterId"],
                ":chapterName" => $data["chapterName"],
                ":age" => $data["age"],
                ":gender" => $data["gender"],
                ":disability" => $data["disability"],
                ":district_code" => $data["district_code"],
                ":district_name" => $data["district_name"],
                ":sector_code" => $data["sector_code"],
                ":sector_name" => $data["sector_name"],
                ":school_code" => $data["school_code"],
                ":school_name" => $data["school_name"],
                ":trainingId" => $data["trainingId"],
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function InsertApprovedSelectedTraineers($data, $logged_user_id)
    {
        if (!array_key_exists('user_id', $data)) {
            $data['user_id'] = $data['staff_code'];
        }
        $currentYear = date("Y");
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
            $insertedRow = $statement->rowCount();
            // trainee is inserted then we add him/her to genral report
            if ($insertedRow) {
                // get available chapters
                $modules = $this->getAllReportsAssignedToTraining($data['trainingId']);
                foreach ($modules as $key => $module) {
                    foreach ($module['details'] as $index => $chapter) {
                        // checking if user has chapter
                        $traineeHasChapter = $this->traineeHasChapterHandler($data, $chapter['cop_report_details_id']);
                        if (count($traineeHasChapter) == 0) {
                            // get trainee information
                            $userDetails = $this->getTraineeInfo($data['user_id'])[0];
                            // get age from dob
                            $age = null;
                            if (isset($userDetails["dob"])) {
                                $dob = DateTime::createFromFormat("Y-m-d", $userDetails["dob"]);
                                $age = $currentYear - $dob->format("Y");
                            }
                            // get school location
                            $schoolLocation = $this->getTraineeSchoolLactionInfo($data['school_code']);
                            // insert trainee to general report
                            $traineeInfo = array(
                                "traineeId" => $data["traineesId"],
                                "userId" => $data["user_id"],
                                "traineeName" => $data["full_name"],
                                "traineePhone" => $data["traineePhone"],
                                "staff_code" => $userDetails["staff_code"],
                                "cohortId" => $data["cohortId"],
                                "moduleId" => $module['cop_report_id'],
                                "moduleName" => $module['cop_report_title'],
                                "chapterId" => $chapter["cop_report_details_id"],
                                "chapterName" => $chapter["cop_report_details_title"],
                                "age" => $age,
                                "gender" => $userDetails["sex"],
                                "disability" => $userDetails["disability"],
                                "district_code" => $schoolLocation["district_code"],
                                "district_name" => $schoolLocation["district_name"],
                                "sector_code" => $schoolLocation["sector_code"],
                                "sector_name" => $schoolLocation["sector_name"],
                                "school_code" => $schoolLocation["school_code"],
                                "school_name" => $schoolLocation["school_name"],
                                "trainingId" => $data["trainingId"],
                            );
                            $this->insertTraineeToGeneralReport($traineeInfo);
                        }
                    }
                }
            }
            return $statement->rowCount();
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
            $statement = "SELECT * FROM cohortconditions CC WHERE CC.cohortId = ? AND CC.district_code = $userDistrictCode";
        } else {
            $statement = "SELECT * FROM cohortconditions CC WHERE CC.cohortId = ?";
        }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($cohortId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                $traineers = $this->countTraineersOnCondition($value);
                $results[$key]['providedTrainees'] = sizeof($traineers);
            }
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getTrainees($conditionId, $userDistrictCode = "")
    {
        if (isset($userDistrictCode) && $userDistrictCode !== "") {
            $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name, UR.role_id, UR.qualification_id, UR.position_code, UR.status, U.staff_code, U.email, U.nid, U.sex FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            INNER JOIN user_to_role UR ON T.userId = UR.user_id
            INNER JOIN users U ON U.user_id = UR.user_id
            WHERE T.cohortId = ? AND UR.status = ? AND T.district_code = $userDistrictCode";
        } else {
            $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name, UR.role_id, UR.qualification_id, UR.position_code, UR.status, U.staff_code, U.email, U.nid, U.sex FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            INNER JOIN user_to_role UR ON T.userId = UR.user_id
            INNER JOIN users U ON U.user_id = UR.user_id
            WHERE T.cohortId = ? AND UR.status = ?";
        }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($conditionId, "Active"));
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

    public function getTeacherByConditionsLimit($condition, $limit, $offset = 0)
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
        $statement = "SELECT TCH.teacher_code, U.user_id,U.full_name, U.staff_code, U.phone_numbers, MIN(SH.combination_name) as combination_name, MIN(SH.grade_name) as grade_name, GROUP_CONCAT(SH.course_name) as course_name, TCH.status, MIN(S.school_name) as school_name, MIN(S.school_code) as school_code, MIN(UR.sector_code) as sector_code, MIN(UR.district_code) as district_code FROM user_to_role UR
        INNER JOIN users U ON  UR.user_id = U.user_id
        INNER JOIN schools S ON S.school_code = UR.school_code
        INNER JOIN teacher_study_hierarchy TCH ON TCH.teacher_code = U.staff_code
        INNER JOIN study_hierarchy SH ON SH.studyhierarchyid = TCH.study_hierarchy_id
        WHERE S.school_code LIKE '$likeSchoolcode%' AND TCH.status = 1
        AND UR.status = 'Active' $sqlConditionString
        GROUP BY U.user_id LIMIT $offset, $limit";
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
