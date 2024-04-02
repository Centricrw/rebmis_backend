<?php
namespace Src\Models;

use Error;

class ReportModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getGeneralReport()
    {
        $statement = " SELECT * FROM general_report WHERE status = 'Active'";
        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getGeneralReportPerTraining($trainingId)
    {
        $statement = "SELECT * FROM general_report WHERE trainingId = $trainingId AND status = 'Active'";
        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getGeneralReportPerTrainee($staff_code, $cohort_id)
    {
        $statement = "SELECT * FROM general_report WHERE staff_code = :staff_code AND cohortId = :cohortId AND status = 'Active'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":staff_code" => $staff_code, ":cohortId" => $cohort_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getGeneralReportPerTrainingForSchool($trainingId, $schoolCode)
    {
        $statement = "SELECT * FROM `general_report` WHERE `trainingId` = :trainingId AND `school_code` = :school_code AND status = 'Active'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':trainingId' => $trainingId,
                ':school_code' => $schoolCode,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function markTheTrainee($data)
    {
        $markType = $data['markType'];
        $statement = "UPDATE general_report SET $markType = :marks WHERE userId = :userId AND chapterId = :chapterId AND cohortId = :cohortId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":userId" => $data['userId'],
                ":cohortId" => $data['cohortId'],
                ":chapterId" => $data['chapterId'],
                ":marks" => $data['marks'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function headTeacherTraineeMarking($data)
    {
        $markType = $data['markType'];
        $statement = "UPDATE `general_report` SET `reflectionNotes` = :reflectionNotes, `classroomApplication` = :classroomApplication WHERE userId = :userId AND chapterId = :chapterId AND cohortId = :cohortId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":userId" => $data['userId'],
                ":chapterId" => $data['chapterId'],
                ":cohortId" => $data['cohortId'],
                ":reflectionNotes" => $data['reflectionNotes'],
                ":classroomApplication" => $data['classroomApplication'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateelearningselfassesment($data)
    {
        $results = 0;
        foreach ($data as $key => $teacher) {
            $staff_code = $teacher['Staff_code'];
            foreach ($teacher as $key => $value) {
                $chapterId = $key;
                $marks = 0;
                if ($value === 'Completed') {$marks = 100;}
                $results += (int) ($this->updateSelfAssessment($chapterId, $marks, $staff_code));
            }
        }
        return $results;
    }

    private function updateSelfAssessment($chapterId, $marks, $staff_code)
    {
        $statement = "UPDATE general_report SET selfAssesment = '" . $marks . "' WHERE staff_code = '" . $staff_code . "' AND chapterId = '" . $chapterId . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function updateElearningMarks($data)
    {
        $results = '';
        foreach ($data as $key => $teacherMarks) {
            $Ururimi_mvugo_nav = $teacherMarks['Ururimi_mvugo_nav'];
            if ($Ururimi_mvugo_nav == '-') {$Ururimi_mvugo_nav = 0;} else { $Ururimi_mvugo_nav = 100;}
            $Ururimi_mvugo_quiz = $teacherMarks['Ururimi_mvugo_quiz'];
            if ($Ururimi_mvugo_quiz == '-') {$Ururimi_mvugo_quiz = 0;}

            $Itahuramajwi_nav = $teacherMarks['Itahuramajwi_nav'];
            if ($Itahuramajwi_nav == '-') {$Itahuramajwi_nav = 0;} else { $Itahuramajwi_nav = 100;}
            $Itahuramajwi_quiz = $teacherMarks['Itahuramajwi_quiz'];
            if ($Itahuramajwi_quiz == '-') {$Itahuramajwi_quiz = 0;}

            $Inyunguramagambo_nav = $teacherMarks['Inyunguramagambo_nav'];
            if ($Inyunguramagambo_nav == '-') {$Inyunguramagambo_nav = 0;} else { $Inyunguramagambo_nav = 100;}
            $Inyunguramagambo_quiz = ((int) $teacherMarks['Inyunguramagambo_quiz']) * 10;
            if ($Inyunguramagambo_quiz == '-') {$Inyunguramagambo_quiz = 0;}

            $Imbumbanyigisho_ya_1_quiz = $teacherMarks['Imbumbanyigisho_ya_1_quiz'];
            if ($Imbumbanyigisho_ya_1_quiz == '-') {$Imbumbanyigisho_ya_1_quiz = 0;}

            $Ihuzamajwi_no_gusoma_ugemura_nav = $teacherMarks['Ihuzamajwi_no_gusoma_ugemura_nav'];
            if ($Ihuzamajwi_no_gusoma_ugemura_nav == '-') {$Ihuzamajwi_no_gusoma_ugemura_nav = 0;} else { $Ihuzamajwi_no_gusoma_ugemura_nav = 100;}
            $Ihuzamajwi_no_gusoma_ugemura_quiz = $teacherMarks['Ihuzamajwi_no_gusoma_ugemura_quiz'];
            if ($Ihuzamajwi_no_gusoma_ugemura_quiz == '-') {$Ihuzamajwi_no_gusoma_ugemura_quiz = 0;}

            $Gusoma_udategwa_nav = $teacherMarks['Gusoma_udategwa_nav'];
            if ($Gusoma_udategwa_nav == '-') {$Gusoma_udategwa_nav = 0;} else { $Gusoma_udategwa_nav = 100;}
            $Gusoma_udategwa_quiz = $teacherMarks['Gusoma_udategwa_quiz'];
            if ($Gusoma_udategwa_quiz == '-') {$Gusoma_udategwa_quiz = 0;}

            $Kumva_umwandiko_nav = $teacherMarks['Kumva_umwandiko_nav'];
            if ($Kumva_umwandiko_nav == '-') {$Kumva_umwandiko_nav = 0;} else { $Kumva_umwandiko_nav = 100;}
            $Kumva_umwandiko_quiz = $teacherMarks['Kumva_umwandiko_quiz'];
            if ($Kumva_umwandiko_quiz == '-') {$Kumva_umwandiko_quiz = 0;}

            $Imbumbanyigisho_ya_2_quiz = ((int) $teacherMarks['Imbumbanyigisho_ya_2_quiz']) * 10;
            if ($Imbumbanyigisho_ya_2_quiz == '-') {$Imbumbanyigisho_ya_2_quiz = 0;}

            $Kwandika_nav = $teacherMarks['Kwandika_nav'];
            if ($Kwandika_nav == '-') {$Kwandika_nav = 0;} else { $Kwandika_nav = 100;}
            $Kwandika_quiz = ((int) $teacherMarks['Kwandika_quiz']) * 10;
            if ($Kwandika_quiz == '-') {$Kwandika_quiz = 0;}

            $Ihangamwandiko_nav = $teacherMarks['Ihangamwandiko_nav'];
            if ($Ihangamwandiko_nav == '-') {$Ihangamwandiko_nav = 0;} else { $Ihangamwandiko_nav = 100;}
            $Ihangamwandiko_quiz = $teacherMarks['Ihangamwandiko_quiz'];
            if ($Ihangamwandiko_quiz == '-') {$Ihangamwandiko_quiz = 0;}

            $Imbumbanyigisho_ya_3_quiz = $teacherMarks['Imbumbanyigisho_ya_3_quiz'];
            if ($Imbumbanyigisho_ya_3_quiz == '-') {$Imbumbanyigisho_ya_3_quiz = 0;}

            $Ibyiciro_byo_gusoma_no_kwandika_nav = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_nav'];
            if ($Ibyiciro_byo_gusoma_no_kwandika_nav == '-') {$Ibyiciro_byo_gusoma_no_kwandika_nav = 0;} else { $Ibyiciro_byo_gusoma_no_kwandika_nav = 100;}
            $Ibyiciro_byo_gusoma_no_kwandika_quiz = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_quiz'];
            if ($Ibyiciro_byo_gusoma_no_kwandika_quiz == '-') {$Ibyiciro_byo_gusoma_no_kwandika_quiz = 0;}

            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav'];
            if ($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav == '-') {$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = 0;} else { $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = 100;}
            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz'];
            if ($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz == '-') {$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz = 0;}

            $Imbumbanyigisho_ya_4_quiz = ($teacherMarks['Imbumbanyigisho_ya_4_quiz']);
            if ($Imbumbanyigisho_ya_4_quiz == '-') {$Imbumbanyigisho_ya_4_quiz = 0;}

            $staff_code = $teacherMarks['staff_code'];

            $end_of_course_quiz = $teacherMarks['end_of_course_quiz'];
            if ($end_of_course_quiz == '-') {$end_of_course_quiz = 0;}

            $results .= $this->updateUrurimi_mvugo($Ururimi_mvugo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateItahuramajwi($Itahuramajwi_nav, $Itahuramajwi_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateInyunguramagambo($Inyunguramagambo_nav, $Inyunguramagambo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateIhuzamajwi_no_gusoma_ugemura($Ihuzamajwi_no_gusoma_ugemura_nav, $Ihuzamajwi_no_gusoma_ugemura_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateGusoma_udategwa($Gusoma_udategwa_nav, $Gusoma_udategwa_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateKumva_umwandiko($Kumva_umwandiko_nav, $Kumva_umwandiko_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateKwandika($Kwandika_nav, $Kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateIhangamwandiko($Ihangamwandiko_nav, $Ihangamwandiko_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateIbyiciro_byo_gusoma_no_kwandika($Ibyiciro_byo_gusoma_no_kwandika_nav, $Ibyiciro_byo_gusoma_no_kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code);
            $results .= $this->updateGuhuza_imyigishirize_yIkinyarwanda_nIcyongereza($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav, $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code);
        }
        return $results;
    }

    private function updateUrurimi_mvugo($Ururimi_mvugo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Ururimi_mvugo_nav . "',
        endOfChapter = '" . $Ururimi_mvugo_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_1_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '4114375c-47bb-4d64-84b2-cea34ee7373e' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateItahuramajwi($Itahuramajwi_nav, $Itahuramajwi_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Itahuramajwi_nav . "',
        endOfChapter = '" . $Itahuramajwi_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_1_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = 'a6e98e88-3f30-4240-96d6-632a00675ee5' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateInyunguramagambo($Inyunguramagambo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Inyunguramagambo_nav . "',
        endOfChapter = '" . $Ururimi_mvugo_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_1_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '0a0a28e4-8799-44ec-8ffb-7707295660ea' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateIhuzamajwi_no_gusoma_ugemura($Ihuzamajwi_no_gusoma_ugemura_nav, $Ihuzamajwi_no_gusoma_ugemura_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Ihuzamajwi_no_gusoma_ugemura_nav . "',
        endOfChapter = '" . $Ihuzamajwi_no_gusoma_ugemura_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_2_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '2493891a-2d15-48b2-9dbb-e2b8653cc595' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateGusoma_udategwa($Gusoma_udategwa_nav, $Gusoma_udategwa_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Gusoma_udategwa_nav . "',
        endOfChapter = '" . $Gusoma_udategwa_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_2_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = 'ab0482b2-4e0f-4836-be9e-644e94a032cf' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateKumva_umwandiko($Kumva_umwandiko_nav, $Kumva_umwandiko_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Kumva_umwandiko_nav . "',
        endOfChapter = '" . $Kumva_umwandiko_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_2_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '79ed2c47-e20f-44db-98a6-70e8f28b4034' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateKwandika($Kwandika_nav, $Kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Kwandika_nav . "',
        endOfChapter = '" . $Kwandika_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_3_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = 'bfc0be81-ad35-4aff-aeec-82265c166396' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateIhangamwandiko($Ihangamwandiko_nav, $Ihangamwandiko_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Ihangamwandiko_nav . "',
        endOfChapter = '" . $Ihangamwandiko_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_3_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = 'd2914b04-e5ae-4bac-a3d6-12659d26aec4' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateIbyiciro_byo_gusoma_no_kwandika($Ibyiciro_byo_gusoma_no_kwandika_nav, $Ibyiciro_byo_gusoma_no_kwandika_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Ibyiciro_byo_gusoma_no_kwandika_nav . "',
        endOfChapter = '" . $Ibyiciro_byo_gusoma_no_kwandika_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_4_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '161e713e-7c88-495f-acda-2c7d830ffb88' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }
    private function updateGuhuza_imyigishirize_yIkinyarwanda_nIcyongereza($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav, $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code)
    {
        $statement = "UPDATE general_report SET
        courseNavigation = '" . $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav . "',
        endOfChapter = '" . $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz . "',
        endOfModule = '" . $Imbumbanyigisho_ya_4_quiz . "',
        endOfCourse = '" . $end_of_course_quiz . "'
         WHERE
         chapterId = '32157200-c599-4e0f-b21e-699f1e49b0e4' AND
         staff_code = '" . $staff_code . "'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }

    }

    public function updateTraineeGeneralReportStatus($status, $trainee_id)
    {
        try {
            $statement = "UPDATE `general_report` SET `status`= :status WHERE `traineeId`= :traineeId";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":status" => $status,
                ":traineeId" => $trainee_id,
            ));

            $result = $statement->rowCount();
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function selectGeneralReportByStatus($status)
    {
        try {
            $statement = "SELECT * FROM `general_report` WHERE `status` = :status";
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":status" => $status,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function selectCountGeneralReportByTraining($trainingId)
    {
        try {
            $statement = "SELECT userId, trainingId, COUNT(*) AS count FROM general_report WHERE `trainingId` = :trainingId GROUP BY userId;";
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":trainingId" => $trainingId,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function selectGeneralReportByTraineeId($trainee_id)
    {
        try {
            $statement = "SELECT * FROM `general_report` WHERE `traineeId` = :traineeId";
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":traineeId" => $trainee_id,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function selectGeneralReportById($generalReportID)
    {
        try {
            $statement = "SELECT * FROM `general_report` WHERE `generalReportId` = :generalReportId LIMIT 1";
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":generalReportId" => $generalReportID,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function updateTeacherChapterMarks($data, $generalReportID)
    {
        try {
            $statement = "UPDATE `general_report` SET `courseNavigation` = :courseNavigation, `endOfChapter` = :endOfChapter, `selfAssesment` = :selfAssesment, `endOfModule` = :endOfModule, `endOfCourse` = :endOfCourse, `copMarks` = :copMarks, `reflectionNotes` = :reflectionNotes, `classroomApplication` = :classroomApplication, `selfStudy` = :selfStudy, `coaching` = :coaching WHERE generalReportId = :generalReportId";

            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":courseNavigation" => $data['courseNavigation'],
                ":endOfChapter" => $data['endOfChapter'],
                ":selfAssesment" => $data['selfAssesment'],
                ":endOfModule" => $data['endOfModule'],
                ":endOfCourse" => $data['endOfCourse'],
                ":copMarks" => $data['copMarks'],
                ":reflectionNotes" => $data['reflectionNotes'],
                ":classroomApplication" => $data['classroomApplication'],
                ":selfStudy" => $data['selfStudy'],
                ":coaching" => $data['coaching'],
                ":generalReportId" => $generalReportID,

            ));
            return $statement->rowCount();
        } catch (\Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}
