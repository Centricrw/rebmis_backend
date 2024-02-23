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
        $statement = " SELECT * FROM general_report";
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
        $statement = "SELECT * FROM general_report WHERE trainingId = $trainingId";
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
        $statement = "SELECT * FROM general_report WHERE staff_code = :staff_code AND cohortId = :cohortId";
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
        $statement = "SELECT * FROM `general_report` WHERE `trainingId` = :trainingId AND `school_code` = :school_code";
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

    public function updateElearningMarks($data)
    {
        foreach ($data as $key => $teacherMarks) {
            $Ururimi_mvugo_nav                  = $teacherMarks['Ururimi_mvugo_nav'];
            $Ururimi_mvugo_quiz                 = $teacherMarks['Ururimi_mvugo_quiz'];
            $Itahuramajwi_nav                   = $teacherMarks['Itahuramajwi_nav'];
            $Itahuramajwi_quiz                  = $teacherMarks['Itahuramajwi_quiz'];
            $Inyunguramagambo_nav               = $teacherMarks['Inyunguramagambo_nav'];
            $Inyunguramagambo_quiz              = $teacherMarks['Inyunguramagambo_quiz'];
            $Ihuzamajwi_no_gusoma_ugemura_quiz  = $teacherMarks['Ihuzamajwi_no_gusoma_ugemura_quiz'];
            $Gusoma_udategwa_nav                = $teacherMarks['Gusoma_udategwa_nav'];
            $Gusoma_udategwa_quiz               = $teacherMarks['Gusoma_udategwa_quiz'];
            $Kumva_umwandiko_nav                = $teacherMarks['Kumva_umwandiko_nav'];
            $Kumva_umwandiko_quiz               = $teacherMarks['Kumva_umwandiko_quiz'];
            $Imbumbanyigisho_ya_2_quiz          = $teacherMarks['Imbumbanyigisho_ya_2_quiz'];
            $Kwandika_nav                       = $teacherMarks['Kwandika_nav'];
            $Kwandika_quiz                      = $teacherMarks['Kwandika_quiz'];
            $Ihangamwandiko_nav                 = $teacherMarks['Ihangamwandiko_nav'];
            $Ihangamwandiko_quiz                = $teacherMarks['Ihangamwandiko_quiz'];
            $Imbumbanyigisho_ya_3_quiz          = $teacherMarks['Imbumbanyigisho_ya_3_quiz'];
            $Ibyiciro_byo_gusoma_no_kwandika_nav = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_nav'];
            $Ibyiciro_byo_gusoma_no_kwandika_quiz = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_quiz'];
            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav'];
            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz'];
            $Imbumbanyigisho_ya_4_nav           = $teacherMarks['Imbumbanyigisho_ya_4_nav'];
            $staff_code                         = $teacherMarks['staff_code'];
            $Imbumbanyigisho_ya_1_quiz  = $teacherMarks['Imbumbanyigisho_ya_1_quiz'];
            $Imbumbanyigisho_ya_2_quiz  = $teacherMarks['Imbumbanyigisho_ya_2_quiz'];
            $Imbumbanyigisho_ya_3_quiz  = $teacherMarks['Imbumbanyigisho_ya_3_quiz'];
            $Imbumbanyigisho_ya_4_quiz  = $teacherMarks['Imbumbanyigisho_ya_4_quiz'];
            $end_of_course_quiz         = $teacherMarks['end_of_course_quiz'];

        }
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Ururimi_mvugo_nav."', 
        endOfChapter = '".$Ururimi_mvugo_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_1_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '800a462f-5e81-45e3-9750-8c99311d2736' AND 
         staff_code = '".$staff_code."'";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
