<?php
namespace Src\Controller;

use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class CandidatesTestController
{
    private $db;
    private $usersModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->usersModel = new UsersModel($db);
    }
    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->convertJsonAndCheckIfStaffCodeExists();
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

    /**
     * getting convert json and checking available staffCode
     * @param NULL
     * @return OBJECT $results
     */
    public function convertJsonAndCheckIfStaffCodeExists()
    {
        // getting authorized user id
        // Cohort_Course_Grades.json
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // Read the JSON file
            $json = file_get_contents('./public/Cohort_Course_Grades.json');

            // Check if the file was read successfully
            if ($json === false) {
                return Errors::badRequestError('Error reading the JSON file');
            }
            // Decode the JSON file
            $json_data = json_decode($json, true);

            // Check if the JSON was decoded successfully
            if ($json_data === null) {
                return Errors::badRequestError('Error decoding the JSON file');
            }
            // Check if the staffCode exists
            $existsStaffCode = [];
            foreach ($json_data as $key => $value) {
                $user = $this->usersModel->findUserByStaffCodeShort($value['ID number']);
                if (count($user) > 0) {
                    $existsStaffCode[] = $value;
                }
            }
            // after checking save candidate which is on database
            // save data to json file
            $encodeData = json_encode($existsStaffCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents("./public/bulk_cohort_data.json", $encodeData);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                'message' => "Created",
                'current_length' => count($json_data),
                'verified_length' => count($existsStaffCode),
                'data' => $existsStaffCode,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}

$controller = new CandidatesTestController($this->db, $request_method, $params);
$controller->processRequest();
