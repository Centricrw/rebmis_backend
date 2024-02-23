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
            $Kwandika_quiz                      = $teacherMarks['Kwandika_quiz'];
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
    private function updateItahuramajwi($Itahuramajwi_nav, $Itahuramajwi_quiz, $Imbumbanyigisho_ya_1_quiz, $end_of_course_quiz, $staff_code){
        $statement = "UPDATE general_report SET 
        courseNavigation = '".$Itahuramajwi_nav."', 
        endOfChapter = '".$Itahuramajwi_quiz."', 
        endOfModule = '".$Imbumbanyigisho_ya_1_quiz."', 
        endOfCourse = '".$end_of_course_quiz."'
         WHERE 
         chapterId = 'a9e267bd-8965-42c5-8252-52ba1039ff20' AND 
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
         chapterId = '9356f656-d400-41da-b16e-201ec8def787' AND 
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
         chapterId = '76866db5-95fd-4db3-ac9b-d9563b5a56de' AND 
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
         chapterId = '4402bfac-5de4-4bdf-b924-1c6a666ce5bc' AND 
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
         chapterId = '75bd8c9a-8b10-468a-9ed4-70519dccd431' AND 
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
         chapterId = '8fe948cf-0b90-49ea-9be0-9ae7650a25f5' AND 
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
         chapterId = '9284cede-c520-42ee-9656-0faabb31de6c' AND 
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
         chapterId = 'd9d5e9cb-01d0-48a9-853b-dd3e68f689f8' AND 
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
         chapterId = '93f805de-4e39-4111-bae1-ea2f4e74a282' AND 
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
