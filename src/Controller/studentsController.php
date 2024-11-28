<?php
namespace Src\Controller;

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use Src\Models\StudentsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class BrandsController
{
    private $db;
    private $studentsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->studentsModel = new StudentsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "one") {
                    // get student by student_code
                    $response = $this->getStudentByStudentCode($this->params['id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "academic") {
                    // get student by academic year
                    $response = $this->getStudentByAcademicYear($this->params['id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "unidentified") {
                    // get unidentified students
                    $response = $this->getUnIdentifiedStudents();
                } else {
                    $response = $this->getAllStudents();
                }
                break;
            case "POST":
                if (isset($this->params['action']) && $this->params['action'] == "many") {
                    // create many new students
                    $response = $this->createManyStudents();
                } else {
                    // create new student
                    $response = $this->createNewStudents();
                }
                break;
            case "PUT":
                if (isset($this->params['action']) && $this->params['action'] == "update") {
                    if (isset($this->params['id'])) {
                        $response = $this->updateStudentsManually($this->params['id']);
                    } else {
                        $response = Errors::unprocessableEntityResponse("Student ID is required for update operation.");
                    }
                } elseif (isset($this->params['action']) && $this->params['action'] == "update_automatically") {
                    $response = $this->updateUnidentifiedStudentsAutomatically();
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            default:
                $response = Errors::notFoundError("Route not found!");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    function getStudentFromSdms($student)
    {
        $url = "https://elearning.reb.rw/sandbox/local/custom_service/userregister.php?type=student&sdmscode=" . trim($student['student_code']);
        try {
            // Fetching the content
            $response = file_get_contents($url);
            if ($response === false) {
                return $student;
            }
            // Decode JSON response to associative array
            $studentFromSdms = json_decode($response, true);
            // Check if JSON decoding was successful
            if ($studentFromSdms === null || !is_array($studentFromSdms['sdms_data'])) {
                return $student;
            }
            return [
                'student_code' => $studentFromSdms['sdms_data']['externalid'],
                'is_from_sdms' => true,
                'academicYear' => str_replace("/", "-", $studentFromSdms['sdms_data']['academicyear']),
                'identification' => $studentFromSdms['sdms_data']['idnumber'],
                'full_name' => $studentFromSdms['sdms_data']['firstname'] . " " . $studentFromSdms['sdms_data']['lastname'],
                'first_name' => $studentFromSdms['sdms_data']['firstname'],
                'middle_name' => $studentFromSdms['sdms_data']['middlename'] ?? null,
                'last_name' => $studentFromSdms['sdms_data']['lastname'],
                'gender' => $studentFromSdms['sdms_data']['gender'] === "MALE" ? "MALE" : "FEMALE",
                'dob' => $studentFromSdms['sdms_data']['dob'],
                'phone_number' => $studentFromSdms['sdms_data']['phone1'] ?? null,
                'email' => $studentFromSdms['sdms_data']['email'] ?? null,
                'schoolCode' => $studentFromSdms['sdms_data']['schoolcode'],
                'address_description' => $studentFromSdms['sdms_data']['address'],
                'country' => $studentFromSdms['sdms_data']['country'],
                'city' => "KIGALI",
                'country_code' => $studentFromSdms['sdms_data']['country_code'] ?? null,
                'class_level_name' => $studentFromSdms['sdms_data']['streamname'] ?? null,
                'class_grade_code' => $studentFromSdms['sdms_data']['classgradecode'] ?? null,
                'class_grade_name' => $studentFromSdms['sdms_data']['classgradename'] ?? null,
                'combination_code' => $studentFromSdms['sdms_data']['combinationcode'] ?? null,
                'isActive' => $studentFromSdms['sdms_data']['active'] === 1,
            ];
        } catch (\Exception $e) {
            return $student;
        }
    }

    function validateStudent($student)
    {
        $errors = [];

        // Validate student_code: required, numeric, and exactly 12 digits
        if (empty($student['student_code']) || !is_numeric($student['student_code']) || strlen($student['student_code']) !== 12) {
            $errors[] = "Invalid student code. It must be a 12-digit number.";
        }

        // Validate full_name: required, string
        if (empty($student['full_name']) || !is_string($student['full_name'])) {
            $errors[] = "Full name is required and must be a string.";
        }

        // Validate first_name: required, string
        if (empty($student['first_name']) || !is_string($student['first_name'])) {
            $errors[] = "First name is required and must be a string.";
        }

        // Validate middle_name: can be null or a string
        if (isset($student['middle_name']) && !is_string($student['middle_name']) && $student['middle_name'] !== null) {
            $errors[] = "Middle name must be a string or null.";
        }

        // Validate last_name: required, string
        if (empty($student['last_name']) || !is_string($student['last_name'])) {
            $errors[] = "Last name is required and must be a string.";
        }

        // Validate schoolCode: required, numeric
        if (empty($student['schoolCode']) || !is_numeric($student['schoolCode'])) {
            $errors[] = "School code is required and must be numeric.";
        }

        // Validate academicYear: required, format YYYY-YYYY
        if (empty($student['academicYear']) || !preg_match('/^\d{4}-\d{4}$/', $student['academicYear'])) {
            $errors[] = "Academic year is required and must follow the format YYYY-YYYY.";
        }

        // Return validation result
        if (!empty($errors)) {
            return ['isValid' => false, 'errors' => $errors];
        } else {
            return ['isValid' => true, 'errors' => []];
        }
    }

    /**
     * Create new Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewStudents()
    {
        // getting input data
        $student = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if data is valid
            $validationResult = $this->validateStudent($student);
            if (!$validationResult['isValid']) {
                return Errors::badRequestError($validationResult['errors'][0]);
            }
            // checking if students code exists
            // Remove white spaces from both sides of a string
            $student_code = trim($student['student_code']);
            $student['is_from_sdms'] = false;
            $studentCodeExists = $this->studentsModel->getStudentsByStudentCode($student_code);
            if (sizeof($studentCodeExists) > 0) {
                return Errors::badRequestError("Student code already exists, please try again?");
            }
            // get student from sdms
            $newStudent = $this->getStudentFromSdms($student);
            // Generate brand id
            $generated_students_id = UuidGenerator::gUuid();
            $newStudent['students_id'] = $generated_students_id;
            $this->studentsModel->createNewStudents($newStudent, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "data" => $newStudent,
                "message" => "Student created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create many students
     * @param OBJECT $data[]
     * @return OBJECT $results
     */

    public function createManyStudents()
    {
        // getting input data
        $students = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // initializing socket
            $socketVersion = new Version2X("http://localhost:6200/socket.io/");
            $socketClient = new Client($socketVersion);
            $socketClient->initialize();

            // getting students
            $existingStudents = array(); // For students found in the database
            $newStudents = array(); // For students not found in the database
            $socketClient->emit('students_received', [
                "message" => "Student received successfully!",
            ]);
            foreach ($students as $student) {
                // checking if students code exists
                // Remove white spaces from both sides of a string
                $student_code = trim($student['student_code']);
                $student['is_from_sdms'] = false;
                $studentCodeExists = $this->studentsModel->getStudentsByStudentCode($student_code);
                if (sizeof($studentCodeExists) > 0) {
                    // get student form sdms
                    $newStudent = $this->getStudentFromSdms($student);
                    array_push($existingStudents, $studentCodeExists[0]);
                    // update students number of tries
                    $newStudent['number_of_tries'] = $studentCodeExists[0]['number_of_tries'] + 1;
                    $newStudent['students_id'] = $studentCodeExists[0]['students_id'];
                    $this->studentsModel->updateStudentsInformationFromSdms($newStudent, $studentCodeExists[0]['created_by']);
                    $socketClient->emit('duplicate_student', $newStudent);
                } else {
                    // get student from sdms
                    $newStudent = $this->getStudentFromSdms($student);
                    // Generate brand id
                    $generated_students_id = UuidGenerator::gUuid();
                    $newStudent['students_id'] = $generated_students_id;
                    $this->studentsModel->createNewStudents($newStudent, $logged_user_id);
                    $socketClient->emit('new_student', $newStudent);
                    array_push($newStudents, $newStudent);
                }
            }
            $socketClient->emit('finished_uploading_students', [
                "message" => "Students finished uploading successfully!",
            ]);
            $socketClient->close();
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "new_students" => $newStudents,
                "existing_students" => $existingStudents,
                "message" => "Student created successfully!",
            ]);
            return $response;
        } catch (\ElephantIO\Exception\ServerConnectionFailureException $e) {
            return Errors::databaseError("Socket.IO connection failed: " . $e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get all student
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllStudents()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->studentsModel->getAllStudents();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get student by student code
     * @param NULL
     * @return OBJECT $results
     */
    public function getStudentByStudentCode($student_code)
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->studentsModel->getStudentsByStudentCode($student_code);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get student by academic year
     * @param NULL
     * @return OBJECT $results
     */
    public function getStudentByAcademicYear($academic_year)
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->studentsModel->getStudentByAcademicYear($academic_year);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get student has not yet unidentified on sdms
     * @param NULL
     * @return OBJECT $results
     */
    public function getUnIdentifiedStudents()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->studentsModel->getStudentsWhoHasNotIdentified();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Parses a given value as an integer.
     *
     * If the value is numeric, it is converted to an integer.
     * Otherwise, the specified default value is returned.
     *
     * @param mixed $value The value to be parsed.
     * @param int $default The default value to return if the input is not numeric.
     *
     * @return int The parsed integer value or the default value.
     */
    function parseIntOrDefault($value, $default = 1)
    {
        if (is_numeric($value)) {
            return intval($value);
        } else {
            return $default;
        }
    }

    /**
     * update student manually
     * @param NULL
     * @return OBJECT $results
     */
    public function updateStudentsManually($student_id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if students exists
            $student = $this->studentsModel->getStudentById($student_id);
            if (count($student) == 0) {
                return Errors::notFoundError("Student not found, please try again?");
            }
            // validate students
            $validate = $this->validateStudent($data);
            if (!$validate['isValid']) {
                return Errors::badRequestError($validate['errors'][0]);
            }
            $data['students_id'] = $student_id;
            $this->studentsModel->updateStudentsInformationFromSdms($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "data" => $data,
                "message" => "Student updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     *  update unidentified student automatically
     * @param NULL
     * @return OBJECT $results
     */
    public function updateUnidentifiedStudentsAutomatically()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // getting unidentified students from sdms
            $unidentifiedStudents = $this->studentsModel->getStudentsWhoHasNotIdentified();
            // updated students count
            $updatedCount = 0;
            if (count($unidentifiedStudents) > 0) {
                foreach ($unidentifiedStudents as $student) {
                    // getting student data from sdms
                    $newStudent = $this->getStudentFromSdms($student);
                    // if students found in sdms count add one
                    $updatedCount = $newStudent['is_from_sdms'] ? $updatedCount + 1 : $updatedCount;
                    // update number of tries
                    $newStudent['number_of_tries'] = $this->parseIntOrDefault($student['number_of_tries']) + 1;
                    $newStudent['students_id'] = $student['students_id'];
                    // update students
                    $this->studentsModel->updateStudentsInformationFromSdms($newStudent, $student['created_by']);
                }
            } else {
                return Errors::notFoundError("No unidentified students found!");
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "data" => [],
                "unidentified_found" => count($unidentifiedStudents),
                "updated_count" => $updatedCount,
                "message" => "Unidentified students updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            //throw $th;
            return Errors::databaseError($th->getMessage());
        }
    }
}
$controller = new BrandsController($this->db, $request_method, $params);
$controller->processRequest();
