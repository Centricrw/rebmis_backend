<?php
namespace Src\Models;

class BulkEnrollModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function bulkEnroll($data)
    {
        $cohort_id = $data['cohort_id'];
        $schools = $data['schools'];
        $newSchools = [];
        foreach ($schools as $school) {
            $newSchool = [];
            $newSchool['schoolCode'] = $school;
            $newSchool['teachers'] = $this->getSchoolTeachers($school, $cohort_id);
            array_push($newSchools, $newSchool);
        }
        //print_r($newSchools);
        return $newSchools;
    }
}
