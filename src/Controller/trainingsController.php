<?php
namespace Src\Controller;

use Src\Models\TraineersModel;
use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;

class trainingsController
{
    private $db;
    private $trainingsModel;
    private $traineersModel;
    private $userRoleModel;
    private $usersModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingsModel = new TrainingsModel($db);
        $this->traineersModel = new TraineersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->usersModel = new UsersModel($db);
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
                    } elseif ($this->params['action'] == "headteacher") {
                        $response = $this->getTrainingsOnSchool();
                    } elseif ($this->params['action'] == "provider") {
                        $response = $this->getTrainingProvider();
                    } elseif ($this->params['action'] == "status") {
                        $response = $this->getTrainingsByStatusRebUser($this->params['id']);
                    } elseif ($this->params['action'] == "providerusers") {
                        $response = $this->getUsersForTrainingProvider($this->params['id']);
                    } elseif (isset($this->params['training_id']) && isset($this->params['cohort_id'])) {
                        $response = $this->getTrainingsTrainees($this->params['training_id'], $this->params['cohort_id']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = Errors::notFoundError("Route not found!");
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
                    } elseif ($this->params['action'] == "assignuserprovider") {
                        $response = $this->assignUserTrainingProvider();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case "PUT":
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "updateuserprovider") {
                        $response = $this->updateUserTrainingProvider();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = Errors::notFoundError("Route not found!");
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
            if (sizeof($current_user_role) == 0) {
                return Errors::badRequestError("No current role found!, please try again?");
            }

            $user_role_details = $current_user_role[0];
            $role = $user_role_details['role_id'];
            switch ($role) {
                case '1':
                    // Class teacher
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "class");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId'], $user_role_details['school_code'], "School");
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '2':
                    // Head Teacher
                    if (!isset($user_role_details['school_code'])) {
                        return Errors::badRequestError("School not found!, please try again?");
                    }
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "School");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId'], $user_role_details['school_code'], "School");
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '3':
                    // DDE
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "District");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId'], $user_role_details['district_code'], "District");
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '4':
                    // REB
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details);
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '7':
                    // DOS
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "District");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId'], $user_role_details['district_code'], "District");
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '18':
                    // SEO
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "Sector");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId'], $user_role_details['sector_code'], "Sector");
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '26':
                    // TRAINING PROVIDER
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "TRAINING PROVIDER");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '27':
                    // Trainer
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "Trainer");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '28':
                    // DIRECTOR_REB
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details, "DIRECTOR_REB");
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '29':
                    // DERECTOR_USAID
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details);
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                case '30':
                    // DERECTOR_FHI
                    $trainingResults = $this->trainingsModel->getAllTranings($user_role_details);
                    foreach ($trainingResults as $key => $value) {
                        $numberOfTrainees = $this->traineersModel->countTrainees($value['trainingId']);
                        $trainingResults[$key]["trainees"] = $numberOfTrainees[0]['numberOfTrainees'];
                    }
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
                default:
                    $trainingResults = [];
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                    $response['body'] = json_encode($trainingResults);
                    return $response;
            }
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

    private function getTrainingsOnSchool()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        // checking if he is headteacher and active
        $userHasActiveRole = $this->userRoleModel->findCurrentUserRoleShort($logged_user_id);
        $userRole = $userHasActiveRole[0];
        if (sizeof($userHasActiveRole) == 0 || $userRole['role_id'] != "2" || empty($userRole['school_code']) || !isset($userRole['school_code'])) {
            return Errors::notFoundError("Logged user does not have role of head teacher, please try again?");
        }

        $result = $this->trainingsModel->selectTrainingsOnSchool($userRole['school_code']);

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
        try {
            $user_id = AuthValidation::authorized()->id;

            $data = (array) json_decode(file_get_contents('php://input'), true);

            // validation
            $validateTrainingInputData = self::validateNewTraining($data);
            if (!$validateTrainingInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateTrainingInputData['message']);
            }

            $result = $this->trainingsModel->addAtraining($data, $user_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
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

    private function assignUserTrainingProvider()
    {
        try {
            $user_id = AuthValidation::authorized()->id;
            $data = (array) json_decode(file_get_contents('php://input'), true);

            // validation
            $validateTrainingInputData = self::validateNewUserTrainingProvider($data);
            if (!$validateTrainingInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateTrainingInputData['message']);
            }

            // check if user have access role of training provider
            $userHasActiveRole = $this->userRoleModel->findCurrentUserRole($data['user_id']);
            if (count($userHasActiveRole) == 0 || $userHasActiveRole[0]['role_id'] != "26") {
                return Errors::badRequestError("User has no role of training provider!, please try again?");
            }

            // checking if usealredy exists
            $userExists = $this->trainingsModel->selectTrainingProviderUserDetails($data['user_id']);
            if (count($userExists) > 0) {
                return Errors::existError("User already has training provider!, please try again?");
            }

            // assign user to training provider
            // Generate user_to_trainingprovider_id
            $user_to_trainingprovider_id = UuidGenerator::gUuid();
            $data['user_to_trainingprovider_id'] = $user_to_trainingprovider_id;
            $results = $this->trainingsModel->createNewTrainingProviderUser($data, $user_id);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($results);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function updateUserTrainingProvider()
    {
        try {
            $user_id = AuthValidation::authorized()->id;
            $data = (array) json_decode(file_get_contents('php://input'), true);

            // validation
            $validateTrainingInputData = self::validateNewUserTrainingProvider($data);
            if (!$validateTrainingInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateTrainingInputData['message']);
            }

            // checking user_to_trainingprovider_id exists
            $userTrainingprovider = isset($data['user_to_trainingprovider_id']) ? $this->trainingsModel->selectOneTrainingProviderUser($data['user_to_trainingprovider_id']) : [];
            if (count($userTrainingprovider) == 0) {
                return Errors::notFoundError("user_to_trainingprovider_id not found!, please try again?");
            }

            // checking if usealredy exists
            $userExists = $this->trainingsModel->selectTrainingProviderUserDetails($data['user_id']);
            if (count($userExists) > 0 && $data['user_id'] != $userExists[0]['user_id']) {
                return Errors::existError("User already has training provider!, please try again?");
            }

            // update user to training provider
            $results = $this->trainingsModel->updateTrainingProviderUser($data, $user_id);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($results);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getUsersForTrainingProvider($trainingprovider_id)
    {
        try {
            $user_id = AuthValidation::authorized()->id;

            // update user to training provider
            $results = $this->trainingsModel->selectTrainingProviderUsers($trainingprovider_id);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function validateNewUserTrainingProvider($input)
    {
        if (!isset($input['user_id']) || empty($input['user_id'])) {
            return ["validated" => false, "message" => "user_id is not provided!"];
        }
        if (isset($input['user_id'])) {
            // checking if user_id exists
            $userExists = $this->usersModel->findOneUser($input['user_id']);
            if (count($userExists) == 0) {
                return ["validated" => false, "message" => "Invalid user_id !, please tray again?"];
            }
        }

        if (!isset($input['training_provider_id']) || empty($input['training_provider_id'])) {
            return ["validated" => false, "message" => "training_provider_id is not provided!"];
        }
        if (isset($input['training_provider_id'])) {
            // checking if training_provider_id exists
            $trainingProviderExists = $this->trainingsModel->findOneTrainingProvider($input['training_provider_id']);
            if (count($trainingProviderExists) == 0) {
                return ["validated" => false, "message" => "Invalid training_provider_id! , please tray again?"];
            }
        }

        if (!isset($input['status']) || !in_array($input['status'], ["0", "1"])) {
            return ["validated" => false, "message" => "status is not provided!"];
        }

        return ["validated" => true, "message" => "OK"];
    }

    private function validateNewTraining($input)
    {
        if (!isset($input['trainingName']) || empty($input['trainingName'])) {
            return ["validated" => false, "message" => "trainingName is not provided!"];
        }
        if (!isset($input['trainingDescription']) || empty($input['trainingDescription'])) {
            return ["validated" => false, "message" => "trainingDescription is not provided!"];
        }
        if (!isset($input['offerMode']) || empty($input['offerMode'])) {
            return ["validated" => false, "message" => "offerMode is not provided!"];
        }
        if (!isset($input['trainingProviderId']) || empty($input['trainingProviderId'])) {
            return ["validated" => false, "message" => "trainingProviderId is not provided!"];
        }
        if (!isset($input['startDate']) || empty($input['startDate'])) {
            return ["validated" => false, "message" => "startDate is not provided!"];
        }
        if (!isset($input['endDate']) || empty($input['endDate'])) {
            return ["validated" => false, "message" => "endDate is not provided!"];
        }
        if (!isset($input['training_type_id']) || empty($input['training_type_id'])) {
            return ["validated" => false, "message" => "training_type_id is not provided!"];
        }
        return ["validated" => true, "message" => "OK"];
    }

}
$controller = new trainingsController($this->db, $request_method, $params);
$controller->processRequest();
