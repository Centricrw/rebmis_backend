<?php
namespace Src\Controller;

use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\InvalidDataException;

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

    private function FileUpload($file, $fileName)
    {
        $results = new \stdClass;
        $target_dir = "public/uploads/";
        $file_name = $target_dir . basename($file[$fileName]["name"]);
        $file_size = $file[$fileName]['size'];
        $file_tmp = $file[$fileName]['tmp_name'];
        $file_type = $file[$fileName]['type'];
        $tmp = explode('.', $file_name);
        $file_ext = end($tmp);

        // Check if file already exists
        if (file_exists($file_name)) {
            $results->message = "Sorry, $fileName file already exists.";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        // Check if file extension
        $extensions = array("jpeg", "jpg", "png", "pdf");
        if (in_array($file_ext, $extensions) === false) {
            $results->message = "$fileName extension not allowed, please choose a JPEG, PNG, pdf file.";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        // Check if file size
        if ($file_size > 2097152) {
            $results->message = "Sorry, $fileName File size excessed 2 MB";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        move_uploaded_file($file_tmp, SITE_ROOT . "/" . $file_name);
        $results->message = "$fileName file uploaded succesfuly";
        $results->success = true;
        $results->$fileName = $file_name;
        return $results;
    }

    private function getTrainingProvider()
    {
        $user_id = AuthValidation::authorized()->id;
        $result = $this->trainingsModel->getTrainingProvider();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    /**
     * Validate training provider input
     * @param array $post
     * @throws InvalidDataException
     */
    function validatingTrainingProviderInput(array $post)
    {
        // Validate trainingProviderName
        if (isset($post["trainingProviderName"]) && empty($post["trainingProviderName"])) {
            throw new InvalidDataException("Training provider name is Required!");
        }

        // Validate description
        if (isset($post["description"]) && empty($post["description"])) {
            throw new InvalidDataException("Description is Required!");
        }

        // Validate email
        if (!isset($post["email"]) || !is_string($post["email"]) || !filter_var($post["email"], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDataException("Email is Required!");
        }

        // validate address
        if (!isset($post["address"]) || empty($post["address"])) {
            throw new InvalidDataException("Address is Required!");
        }

        // Validate phone number
        if (!isset($post["phone_number"]) || !is_string($post["phone_number"]) || strlen($post["phone_number"]) != 10 || !preg_match('/^07/', $post["phone_number"])) {
            throw new InvalidDataException("Phone number must be a string starting with '07' and have 10 digits");
        }

        /**
         *  Checking if provider name, phone_number and email  exist in table
         * */
        $proverExists = $this->trainingsModel->ProviderExists($post);
        if (sizeof($proverExists) > 0) {
            if ($proverExists[0]['trainingProviderName'] == $post['trainingProviderName']) {
                throw new InvalidDataException("Provider name alredy exists, please try again");
            }

            if ($proverExists[0]['phone_number'] == $_POST['phone_number']) {
                throw new InvalidDataException("Provider phone number alredy exists, please try again");
            }

            if ($proverExists[0]['email'] == $post['email']) {
                throw new InvalidDataException("Provider email alredy exists, please try again");
            }
        };
    }

    private function CreateTrainingProvider()
    {
        try {
            $user_id = AuthValidation::authorized()->id;
            $this->validatingTrainingProviderInput($_POST);

            // upload file
            $fileuploadDocuments = $this->FileUpload($_FILES, "supporting_documents");
            $fileuploadLogo = $this->FileUpload($_FILES, "TrainingProviderlogo");
            if (!$fileuploadDocuments->success && !$fileuploadLogo->success) {
                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => $fileuploadLogo->message . " and " . $fileuploadDocuments->message,
                ]);
                return $response;
            }

            // insert into table
            $data = (object) $_POST;
            $documents = "supporting_documents";
            $logo = "TrainingProviderlogo";
            $data->$documents = $fileuploadDocuments->$documents;
            $data->$logo = $fileuploadLogo->$logo;
            $result = $this->trainingsModel->CreateTrainingProvider($data, $user_id);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                'message' => "Training provider created succesfully!",
            ]);
            return $response;

        } catch (InvalidDataException $e) {
            return Errors::unprocessableEntityResponse($e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
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
