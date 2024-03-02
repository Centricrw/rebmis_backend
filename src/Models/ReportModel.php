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

    public function updateelearningselfassesment($data)
    {
        $results = '';
        foreach ($data as $key => $teacher) {
            $staff_code = $teacher['Staff_code'];
            foreach ($teacher as $key => $value) {
                $chapterId = $key;
                $marks = 0;
                if($value === 'Completed'){$marks = 100;}
                $results .=$this->updateSelfAssessment($chapterId,$marks,$staff_code);
            }
        }
    }
        
    private function updateSelfAssessment($chapterId,$marks,$staff_code)
    {
        $statement = "UPDATE general_report SET selfAssesment = :marks WHERE staff_code = :staff_code AND chapterId = :chapterId";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":staff_code" => $staff_code,
                ":amount" => $marks,
                ":chapterId" => $chapterId
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function updateElearningMarks($data)
    {
        $results = '';
        foreach ($data as $key => $teacherMarks) {
            $Ururimi_mvugo_nav                  = $teacherMarks['Ururimi_mvugo_nav'];
            if($Ururimi_mvugo_nav == '-'){$Ururimi_mvugo_nav = 0;}else{$Ururimi_mvugo_nav = 100;}
            $Ururimi_mvugo_quiz                 = $teacherMarks['Ururimi_mvugo_quiz'];
            if($Ururimi_mvugo_quiz == '-'){$Ururimi_mvugo_quiz = 0;}

            $Itahuramajwi_nav                   = $teacherMarks['Itahuramajwi_nav'];
            if($Itahuramajwi_nav == '-'){$Itahuramajwi_nav = 0;}else{$Itahuramajwi_nav = 100;}
            $Itahuramajwi_quiz                  = $teacherMarks['Itahuramajwi_quiz'];
            if($Itahuramajwi_quiz == '-'){$Itahuramajwi_quiz = 0;}

            $Inyunguramagambo_nav               = $teacherMarks['Inyunguramagambo_nav'];
            if($Inyunguramagambo_nav == '-'){$Inyunguramagambo_nav = 0;}else{$Inyunguramagambo_nav = 100;}
            $Inyunguramagambo_quiz              = ((int)$teacherMarks['Inyunguramagambo_quiz'])*10;
            if($Inyunguramagambo_quiz == '-'){$Inyunguramagambo_quiz = 0;}



            $Imbumbanyigisho_ya_1_quiz  = $teacherMarks['Imbumbanyigisho_ya_1_quiz'];
            if($Imbumbanyigisho_ya_1_quiz == '-'){$Imbumbanyigisho_ya_1_quiz = 0;}



            $Ihuzamajwi_no_gusoma_ugemura_nav  = $teacherMarks['Ihuzamajwi_no_gusoma_ugemura_nav'];
            if($Ihuzamajwi_no_gusoma_ugemura_nav == '-'){$Ihuzamajwi_no_gusoma_ugemura_nav = 0;}else{$Ihuzamajwi_no_gusoma_ugemura_nav = 100;}
            $Ihuzamajwi_no_gusoma_ugemura_quiz  = $teacherMarks['Ihuzamajwi_no_gusoma_ugemura_quiz'];
            if($Ihuzamajwi_no_gusoma_ugemura_quiz == '-'){$Ihuzamajwi_no_gusoma_ugemura_quiz = 0;}
            
            $Gusoma_udategwa_nav                = $teacherMarks['Gusoma_udategwa_nav'];
            if($Gusoma_udategwa_nav == '-'){$Gusoma_udategwa_nav = 0;}else{$Gusoma_udategwa_nav = 100;}
            $Gusoma_udategwa_quiz               = $teacherMarks['Gusoma_udategwa_quiz'];
            if($Gusoma_udategwa_quiz == '-'){$Gusoma_udategwa_quiz = 0;}
            
            $Kumva_umwandiko_nav                = $teacherMarks['Kumva_umwandiko_nav'];
            if($Kumva_umwandiko_nav == '-'){$Kumva_umwandiko_nav = 0;}else{$Kumva_umwandiko_nav = 100;}
            $Kumva_umwandiko_quiz               = $teacherMarks['Kumva_umwandiko_quiz'];
            if($Kumva_umwandiko_quiz == '-'){$Kumva_umwandiko_quiz = 0;}



            $Imbumbanyigisho_ya_2_quiz          = ((int)$teacherMarks['Imbumbanyigisho_ya_2_quiz'])*10;
            if($Imbumbanyigisho_ya_2_quiz == '-'){$Imbumbanyigisho_ya_2_quiz = 0;}



            $Kwandika_nav                       = $teacherMarks['Kwandika_nav'];
            if($Kwandika_nav == '-'){$Kwandika_nav = 0;}else{$Kwandika_nav = 100;}
            $Kwandika_quiz                      = ((int)$teacherMarks['Kwandika_quiz']*10);
            if($Kwandika_quiz == '-'){$Kwandika_quiz = 0;}

            $Ihangamwandiko_nav                 = $teacherMarks['Ihangamwandiko_nav'];
            if($Ihangamwandiko_nav == '-'){$Ihangamwandiko_nav = 0;}else{$Ihangamwandiko_nav = 100;}
            $Ihangamwandiko_quiz                = $teacherMarks['Ihangamwandiko_quiz'];
            if($Ihangamwandiko_quiz == '-'){$Ihangamwandiko_quiz = 0;}

            $Imbumbanyigisho_ya_3_quiz          = $teacherMarks['Imbumbanyigisho_ya_3_quiz'];
            if($Imbumbanyigisho_ya_3_quiz == '-'){$Imbumbanyigisho_ya_3_quiz = 0;}



            $Ibyiciro_byo_gusoma_no_kwandika_nav = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_nav'];
            if($Ibyiciro_byo_gusoma_no_kwandika_nav == '-'){$Ibyiciro_byo_gusoma_no_kwandika_nav = 0;}else{$Ibyiciro_byo_gusoma_no_kwandika_nav = 100;}
            $Ibyiciro_byo_gusoma_no_kwandika_quiz = $teacherMarks['Ibyiciro_byo_gusoma_no_kwandika_quiz'];
            if($Ibyiciro_byo_gusoma_no_kwandika_quiz == '-'){$Ibyiciro_byo_gusoma_no_kwandika_quiz = 0;}
            
            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav'];
            if($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav == '-'){$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = 0;}else{$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav = 100;}
            $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz = $teacherMarks['Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz'];
            if($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz == '-'){$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz = 0;}

            $Imbumbanyigisho_ya_4_quiz           = ($teacherMarks['Imbumbanyigisho_ya_4_quiz']);
            if($Imbumbanyigisho_ya_4_quiz == '-'){$Imbumbanyigisho_ya_4_quiz = 0;}


            $staff_code                         = $teacherMarks['staff_code'];

            $end_of_course_quiz         = $teacherMarks['end_of_course_quiz'];
            if($end_of_course_quiz == '-'){$end_of_course_quiz = 0;}

            $results .=$this->updateUrurimi_mvugo($Ururimi_mvugo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateItahuramajwi($Itahuramajwi_nav, $Itahuramajwi_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateInyunguramagambo($Inyunguramagambo_nav, $Inyunguramagambo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateIhuzamajwi_no_gusoma_ugemura($Ihuzamajwi_no_gusoma_ugemura_nav, $Ihuzamajwi_no_gusoma_ugemura_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateGusoma_udategwa($Gusoma_udategwa_nav, $Gusoma_udategwa_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);   
            $results .=$this->updateKumva_umwandiko($Kumva_umwandiko_nav, $Kumva_umwandiko_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateKwandika($Kwandika_nav, $Kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateIhangamwandiko($Ihangamwandiko_nav, $Ihangamwandiko_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateIbyiciro_byo_gusoma_no_kwandika($Ibyiciro_byo_gusoma_no_kwandika_nav, $Ibyiciro_byo_gusoma_no_kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code);
            $results .=$this->updateGuhuza_imyigishirize_yIkinyarwanda_nIcyongereza($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav, $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code);
        }
        return $results;
    }

    private function updateUrurimi_mvugo($Ururimi_mvugo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Ururimi_mvugo_nav."', 
        endOfChapter = '".$Ururimi_mvugo_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_1_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '4114375c-47bb-4d64-84b2-cea34ee7373e' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateItahuramajwi($Itahuramajwi_nav, $Itahuramajwi_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Itahuramajwi_nav."', 
        endOfChapter = '".$Itahuramajwi_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_1_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = 'a6e98e88-3f30-4240-96d6-632a00675ee5' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateInyunguramagambo($Inyunguramagambo_nav, $Ururimi_mvugo_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Inyunguramagambo_nav."', 
        endOfChapter = '".$Ururimi_mvugo_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_1_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '0a0a28e4-8799-44ec-8ffb-7707295660ea' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateIhuzamajwi_no_gusoma_ugemura($Ihuzamajwi_no_gusoma_ugemura_nav, $Ihuzamajwi_no_gusoma_ugemura_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Ihuzamajwi_no_gusoma_ugemura_nav."', 
        endOfChapter = '".$Ihuzamajwi_no_gusoma_ugemura_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_2_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '2493891a-2d15-48b2-9dbb-e2b8653cc595' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateGusoma_udategwa($Gusoma_udategwa_nav, $Gusoma_udategwa_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Gusoma_udategwa_nav."', 
        endOfChapter = '".$Gusoma_udategwa_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_2_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = 'ab0482b2-4e0f-4836-be9e-644e94a032cf' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateKumva_umwandiko($Kumva_umwandiko_nav, $Kumva_umwandiko_quiz, $Imbumbanyigisho_ya_2_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Kumva_umwandiko_nav."', 
        endOfChapter = '".$Kumva_umwandiko_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_2_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '79ed2c47-e20f-44db-98a6-70e8f28b4034' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateKwandika($Kwandika_nav, $Kwandika_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Kwandika_nav."', 
        endOfChapter = '".$Kwandika_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_3_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = 'bfc0be81-ad35-4aff-aeec-82265c166396' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateIhangamwandiko($Ihangamwandiko_nav, $Ihangamwandiko_quiz, $Imbumbanyigisho_ya_3_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Ihangamwandiko_nav."', 
        endOfChapter = '".$Ihangamwandiko_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_3_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = 'd2914b04-e5ae-4bac-a3d6-12659d26aec4' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateIbyiciro_byo_gusoma_no_kwandika($Ibyiciro_byo_gusoma_no_kwandika_nav, $Ibyiciro_byo_gusoma_no_kwandika_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Ibyiciro_byo_gusoma_no_kwandika_nav."', 
        endOfChapter = '".$Ibyiciro_byo_gusoma_no_kwandika_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_4_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '161e713e-7c88-495f-acda-2c7d830ffb88' AND 
         staff_code = '".$staff_code."'";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array());
                return $statement->rowCount();
            } catch (\PDOException $e) {
                throw new Error($e->getMessage());
            }
        
    }
    private function updateGuhuza_imyigishirize_yIkinyarwanda_nIcyongereza($Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav, $Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz, $Imbumbanyigisho_ya_4_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_nav."', 
        endOfChapter = '".$Guhuza_imyigishirize_yIkinyarwanda_nIcyongereza_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_4_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = '32157200-c599-4e0f-b21e-699f1e49b0e4' AND 
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
