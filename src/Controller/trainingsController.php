<?php
namespace Src\Controller;

use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class trainingsController
{
    private $db;
    private $trainingsModel;
    private $userRoleModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingsModel = new TrainingsModel($db);
        $this->userRoleModel = new UserRoleModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getall") {
                        $response = $this->getAllTranings();
                    } elseif ($this->params['action'] == "training") {
                        $response = $this->getOneTraining($this->params['id']);
                    } elseif ($this->params['action'] == "provider") {
                        $response = $this->getTrainingProvider();
                    } elseif ($this->params['action'] == "status") {
                        $response = $this->getTrainingsByStatusRebUser($this->params['id']);
                    } elseif (isset($this->params['training_id']) && isset($this->params['cohort_id'])) {
                        $response = $this->getTrainingsTrainees($this->params['training_id'], $this->params['cohort_id']);
                    } else {
                        // echo ($this->request_path());
                        print_r($this->params);
                        $response = Errors::notFoundError("Route not found!");
                    }
                }
                break;

            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "create") {
                        $response = $this->addAtraining();
                    } elseif ($this->params['action'] == "comfirm") {
                        $response = $this->ComfirmTainingsByReb($this->params['id']);
                    } elseif ($this->params['action'] == "provider") {
                        $response = $this->CreateTrainingProvider();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                }
                break;
            default:
                $response = Errors::notFoundError("no request provided");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllTranings()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($current_user_role) > 0) {
                $userRole = $current_user_role[0]['role_id'];
                $userSchoolCode = $current_user_role[0]['school_code'];
                $userSectorCode = $current_user_role[0]['sector_code'];
                $userDistrictCode = $current_user_role[0]['district_code'];
            }

            $result = $this->trainingsModel->getAllTranings($userDistrictCode);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getOneTraining($training_id)
    {
        $result = $this->trainingsModel->getOneTraining($training_id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getTrainingsTrainees($training_id, $cohort_id)
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $result = $this->trainingsModel->getTrainingsTrainees($training_id, $cohort_id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function addAtraining()
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $data = (array) json_decode(file_get_contents('php://input'), true);

        // Validate input if not empty
        if (!self::validateNewTraining($data)) {
            return Errors::unprocessableEntityResponse();
        }

        $result = $this->trainingsModel->addAtraining($data, $user_id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function ComfirmTainingsByReb($trainings_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        $data = (array) json_decode(file_get_contents('php://input'), true);

        // validate status Rejected, Ongoing
        if (!empty($data['status']) && $data["status"] !== "Rejected" && $data["status"] !== "Ongoing" && $data["status"] !== "Pending" && $data["status"] !== "Ended") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => $data['status'] . ", Invalid Status input, Please try again?",
            ]);
            return $response;
        }

        $user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);

        if (sizeof($user_role) > 0 && strval($user_role[0]['role_id']) !== "4") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Not allowed to view this data, Please contact admistrator?",
            ]);
            return $response;
        }

        // checking if trainings id exists
        $trainingExist = $this->trainingsModel->findTrainingByID($trainings_id);

        if (sizeof($trainingExist) == 0) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Training not found, please try again?",
            ]);
            return $response;
        }
        // update or comfirm trainings status
        $result = $this->trainingsModel->comfirmTraining($trainings_id, $data["status"]);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            "message" => "Trainings status updated successfully!",
        ]);
        return $response;
    }

    private function getTrainingsByStatusRebUser($status)
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;
        $user_role = $this->userRoleModel->findCurrentUserRole($user_id);

        if (sizeof($user_role) > 0 && strval($user_role[0]['role_id']) !== "4") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Not allowed to view this data, Please contact admistrator?",
            ]);
            return $response;
        }

        $result = $this->trainingsModel->getTraningsByStatus($status);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function FileUpload($file)
    {
        $results = new \stdClass;
        $target_dir = "public/uploads/";
        $file_name = $target_dir . basename($file["supporting_documents"]["name"]);
        $file_size = $file['supporting_documents']['size'];
        $file_tmp = $file['supporting_documents']['tmp_name'];
        $file_type = $file['supporting_documents']['type'];
        $tmp = explode('.', $file_name);
        $file_ext = end($tmp);

        // Check if file already exists
        if (file_exists($file_name)) {
            $results->message = "Sorry, file already exists.";
            $results->success = false;
            $results->supporting_documents = $file_name;
            return $results;
        }

        // Check if file extension
        $extensions = array("jpeg", "jpg", "png", "pdf");
        if (in_array($file_ext, $extensions) === false) {
            $results->message = "extension not allowed, please choose a JPEG, PNG, pdf file.";
            $results->success = false;
            $results->supporting_documents = $file_name;
            return $results;
        }

        // Check if file size
        if ($file_size > 2097152) {
            $results->message = "Sorry, File size excessed 2 MB";
            $results->success = false;
            $results->supporting_documents = $file_name;
            return $results;
        }

        move_uploaded_file($file_tmp, SITE_ROOT . "/" . $file_name);
        $results->message = "file uploaded succesfuly";
        $results->success = true;
        $results->supporting_documents = $file_name;
        return $results;
    }

    private function getTrainingProvider()
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }

        // $user_id = AuthValidation::decodedData($jwt_data)->data->id;
        $result = $this->trainingsModel->getTrainingProvider();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function CreateTrainingProvider()
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;
        if (isset($_FILES['supporting_documents']) && $_POST['trainingProviderName']) {

            /**
             * Checking if provider name, phone_number and email  exist in table
             */
            $proverExists = $this->trainingsModel->ProviderExists($_POST);
            if (sizeof($proverExists) > 0) {
                $message = $proverExists[0]['trainingProviderName'] == $_POST['trainingProviderName'] ? "Provider name alredy exists, please try again" : ($proverExists[0]['phone_number'] == $_POST['phone_number'] ? "Provider phone number alredy exists, please try again" : "Provider email alredy exists, please try again");

                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => $message,
                ]);
                return $response;

            };

            // upload file
            $fileupload = $this->FileUpload($_FILES);
            if (!$fileupload->success) {
                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => $fileupload->message,
                ]);
                return $response;
            }
            $data = (object) $_POST;
            $data->supporting_documents = $fileupload->supporting_documents;
            $result = $this->trainingsModel->CreateTrainingProvider($data, $user_id);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                'message' => "Training provider created succesfully!",
            ]);
            return $response;

        }
    }

    private function validateNewTraining($input)
    {
        if (empty($input['trainingName'])) {
            return false;
        }
        if (empty($input['trainingDescription'])) {
            return false;
        }
        if (empty($input['offerMode'])) {
            return false;
        }
        if (empty($input['trainingProviderId'])) {
            return false;
        }
        if (empty($input['startDate'])) {
            return false;
        }
        if (empty($input['endDate'])) {
            return false;
        }
        return true;
    }

}
$controller = new trainingsController($this->db, $request_method, $params);
$controller->processRequest();
