<?php
namespace Src\Controller;

use Src\Models\SchoolLocationModal;
use Src\Models\TeacherStudyHierarchyModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class TeacherStudyHierarchyController
{
    private $db;
    private $schoolLocationModal;
    private $teacherStudyHierarchyModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->schoolLocationModal = new SchoolLocationModal($db);
        $this->teacherStudyHierarchyModel = new TeacherStudyHierarchyModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                $response = $this->assignTeacherCourseToSchool();
                break;
            case 'GET':
                if (sizeof($this->params) > 0) {
                    $response = $this->getStudyHierarchy();
                } else {
                    $response = $this->getStudyHierarchy();
                }
                break;
            default:
                $response = Errors::notFoundError('Route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    /**
     * getting study hierarchy
     * @param null
     * @return array $response
     */
    public function getStudyHierarchy()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // getting study hierarchy
            $results = $this->teacherStudyHierarchyModel->selectStudyHierarchy();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Assign Teacher course to school
     * @param null
     * @return array $response
     */
    public function assignTeacherCourseToSchool()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {

            if (!isset($data['staff_code']) || empty($data['staff_code'])) {
                return Errors::unprocessableEntityResponse("Staff code is required!");
            }
            if (!isset($data['study_hierarchy_id']) || empty($data['study_hierarchy_id'])) {
                return Errors::unprocessableEntityResponse("study hierarchy is required!");
            }

            $dataToInsert = [
                "staff_code" => $data['staff_code'],
                "study_hierarchy_id" => $data['study_hierarchy_id'],
            ];
            $teacherHeirarchyExists = $this->teacherStudyHierarchyModel->findTeacherStudyHierarchy($dataToInsert);
            if (sizeof($teacherHeirarchyExists) == 0) {
                $this->teacherStudyHierarchyModel->insertNewTeacherStudyHierarchy($dataToInsert);
            }
            return $data;
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($dataToInsert);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}

$controller = new TeacherStudyHierarchyController($this->db, $request_method, $params);
$controller->processRequest();
