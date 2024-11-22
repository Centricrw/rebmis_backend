<?php
namespace Src\Models;

use Error;

class StudentsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Create new student
     * @param OBJECT $data
     * @param STRING $created_by
     * @return NUMBER
     */
    public function createNewStudents($data, $created_by)
    {
        $statement = "INSERT INTO `students`(`students_id`, `student_code`, `is_from_sdms`, `academicYear`, `full_name`, `first_name`, `middle_name`, `last_name`, `gender`, `dob`, `identification`, `schoolCode`, `phone_number`, `email`, `address_description`, `country`, `city`, `country_code`, `class_level_name`, `class_grade_code`, `class_grade_name`, `combination_code`, `isActive`, `created_by`) VALUES (:students_id, :student_code, :is_from_sdms, :academicYear, :full_name, :first_name, :middle_name, :last_name, :gender, :dob, :identification, :schoolCode, :phone_number, :email, :address_description, :country, :city, :country_code, :class_level_name, :class_grade_code, :class_grade_name, :combination_code, :isActive, :created_by)";
        try {
            // Remove white spaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':students_id' => $data['students_id'],
                ':student_code' => trim($data['student_code']),
                ':is_from_sdms' => isset($data['is_from_sdms']) && $data['is_from_sdms'] === true ? 1 : 0,
                ':academicYear' => $data['academicYear'],
                ':full_name' => $data['full_name'],
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'] ?? null,
                ':last_name' => $data['last_name'],
                ':gender' => $data['gender'] ?? null,
                ':dob' => $data['dob'] ?? null,
                ':identification' => $data['identification'] ?? null,
                ':schoolCode' => $data['schoolCode'],
                ':phone_number' => $data['phone_number'] ?? null,
                ':email' => $data['email'] ?? null,
                ':address_description' => $data['address_description'] ?? null,
                ':country' => $data['country'] ?? null,
                ':city' => $data['city'] ?? null,
                ':country_code' => $data['country_code'] ?? null,
                ':class_level_name' => $data['class_level_name'] ?? null,
                ':class_grade_code' => $data['class_grade_code'] ?? null,
                ':class_grade_name' => $data['class_grade_name'] ?? null,
                ':combination_code' => $data['combination_code'] ?? null,
                ':isActive' => isset($data['isActive']) && $data['isActive'] === false ? 0 : 1,
                ':created_by' => $created_by,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get all students
     * @return OBJECT $results
     */
    public function getAllStudents()
    {
        $statement = "SELECT ST.*, SC.school_name, SC.school_category, SC.region_code as school_region_code FROM `students` ST LEFT JOIN schools SC ON ST.schoolCode = school_code
         WHERE `status` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get Students by student_code
     * @param NUMBER $student_code
     * @return OBJECT $results
     */
    public function getStudentsByStudentCode($student_code)
    {
        $statement = "SELECT ST.*, SC.school_name, SC.school_category, SC.region_code as school_region_code FROM `students` ST LEFT JOIN schools SC ON ST.schoolCode = school_code WHERE ST.student_code = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($student_code));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get student by id
     * @param STRING $student_id
     * @return OBJECT $results
     */
    public function getStudentById($student_id)
    {
        $statement = "SELECT ST.*, SC.school_name, SC.school_category, SC.region_code as school_region_code FROM `students` ST LEFT JOIN schools SC ON ST.schoolCode = school_code WHERE ST.`students_id` = ? LIMIT 1";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($student_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get student by academic year
     * @param STRING $academic_year
     * @return OBJECT $results
     */
    public function getStudentByAcademicYear($academic_year)
    {
        $statement = "SELECT ST.*, SC.school_name, SC.school_category, SC.region_code as school_region_code FROM `students` ST LEFT JOIN schools SC ON ST.schoolCode = school_code WHERE ST.`academicYear` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($academic_year));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * get students who has not yet identified in sdms code
     * @param STRING $academic_year
     * @return OBJECT $results
     */
    public function getStudentsWhoHasNotIdentified()
    {
        $statement = "SELECT ST.*, SC.school_name, SC.school_category, SC.region_code as school_region_code FROM `students` ST LEFT JOIN schools SC ON ST.schoolCode = school_code WHERE ST.`is_from_sdms` = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(0));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * update student information
     * @param STRING $academic_year
     * @return OBJECT $results
     */
    public function updateStudentsInformationFromSdms($data, $updated_by)
    {
        $statement = "UPDATE `students` SET `student_code`=:student_code, `is_from_sdms`=:is_from_sdms,`academicYear`=:academicYear,`full_name`=:full_name,`first_name`=:first_name,`middle_name`=:middle_name,`last_name`=:last_name,`gender`=:gender,`dob`=:dob,`identification`=:identification,`schoolCode`=:schoolCode,`phone_number`=:phone_number,`email`=:email,`address_description`=:address_description,`country`=:country,`city`=:city,`country_code`=:country_code,`class_level_name`=:class_level_name,`class_grade_code`=:class_grade_code,`class_grade_name`=:class_grade_name,`combination_code`=:combination_code,`isActive`=:isActive,`updated_by`=:updated_by,`status`=:status,`number_of_tries`=:number_of_tries WHERE `students_id`=:students_id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':student_code' => $data['student_code'],
                ':is_from_sdms' => isset($data['is_from_sdms']) && $data['is_from_sdms'] === true ? 1 : 0,
                ':academicYear' => $data['academicYear'],
                ':full_name' => $data['full_name'],
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'] ?? null,
                ':last_name' => $data['last_name'],
                ':gender' => $data['gender'] ?? null,
                ':dob' => $data['dob'] ?? null,
                ':identification' => $data['identification'] ?? null,
                ':schoolCode' => $data['schoolCode'],
                ':phone_number' => $data['phone_number'] ?? null,
                ':email' => $data['email'] ?? null,
                ':address_description' => $data['address_description'] ?? null,
                ':country' => $data['country'] ?? null,
                ':city' => $data['city'] ?? null,
                ':country_code' => $data['country_code'] ?? null,
                ':class_level_name' => $data['class_level_name'] ?? null,
                ':class_grade_code' => $data['class_grade_code'] ?? null,
                ':class_grade_name' => $data['class_grade_name'] ?? null,
                ':combination_code' => $data['combination_code'] ?? null,
                ':isActive' => isset($data['isActive']) && $data['isActive'] === false ? 0 : 1,
                ':updated_by' => $updated_by,
                ':status' => $data['status'] ?? 1,
                ':number_of_tries' => $data['number_of_tries'] ?? 1,
                ':students_id' => $data['students_id'],
            ));
            $results = $statement->rowCount();
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
