<?php
namespace Src\Controller;

use Src\Models\DirectorSignatureModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;
use Src\Validations\BasicValidation;

class directorSignatureController
{
    private $db;
    private $directorSignatureModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->directorSignatureModel = new DirectorSignatureModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "training") {
                        $response = $this->getDirectorSignatureByTrainingId($this->params['id']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = $this->selectDirectorSignatureByUserId();
                }
                break;
            case "POST":
                if (isset($this->params['action']) && $this->params['action'] == "signature") {
                    $response = $this->updateDirectorSignature($this->params['id']);
                } else {
                    $response = $this->createNewDirectorSignature();
                }
                break;
            case "PATCH":
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "information") {
                        $response = $this->updateDirectorSignatureInfo($this->params['id']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
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

    /**
     * Create new director signature
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewDirectorSignature()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // validating input
            $validateThisValues = [
                "user_id" => "User id is required",
                "director_name" => "Director name is required",
                "director_role" => "Director role is required",
                "director_institution" => "Director institution is required",
                "training_id" => "Training id is required",
                "position" => "Position is required",
            ];
            $validateUserInputData = BasicValidation::validate($_POST, $validateThisValues);
            if (!$validateUserInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateUserInputData['message']);
            }

            // check if signature exists
            $signatureExists = $this->directorSignatureModel->selectDirectorSignatureUserTraining($_POST['training_id'], $_POST['user_id']);
            if (count($signatureExists) > 0) {
                return Errors::notFoundError("Signature already exists, please try again?");
            }

            // upload file
            $fileUploadLogo = $this->FileUpload($_FILES, "director_signature_url");
            if (!$fileUploadLogo->success) {
                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => $fileUploadLogo->message,
                ]);
                return $response;
            }

            // Generate id
            $generatedId = UuidGenerator::gUuid();
            // insert into table
            $data = (object) $_POST;
            $file = "director_signature_url";
            $id = "Signator_id";
            $data->$file = $fileUploadLogo->$file;
            $data->$id = $generatedId;
            $data = json_encode($data);
            $insertData = json_decode($data, true);
            $this->directorSignatureModel->insertDirectorSignature($insertData);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Signature added successfully!",
                "data" => $insertData,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get director signature
     * @return OBJECT $results
     */

    public function selectDirectorSignatureByUserId()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->directorSignatureModel->selectDirectorSignatureBYUser($user_id);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get director signature by training
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function getDirectorSignatureByTrainingId($training_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->directorSignatureModel->selectDirectorSignatureBYTraining($training_id);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update director signature information
     * @param STRING $Signator_id
     * @return OBJECT $results
     */

    public function updateDirectorSignatureInfo($Signator_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            // validating input
            $validateThisValues = [
                "director_name" => "Director name is required",
                "director_role" => "Director role is required",
                "director_institution" => "Director institution is required",
                "training_id" => "Training id is required",
                "position" => "Position is required",
            ];
            $validateUserInputData = BasicValidation::validate($data, $validateThisValues);
            if (!$validateUserInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateUserInputData['message']);
            }

            // checking if Signator_id exists
            $signatureExists = $this->directorSignatureModel->selectDirectorSignatureBYId($Signator_id);
            if (count($signatureExists) == 0) {
                return Errors::notFoundError("Signature not found, please try again?");
            }

            // update
            $this->directorSignatureModel->updateDirectorSignatureInfo($Signator_id, $data);

            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode([
                "message" => "Signature information updated successfully!",
                "data" => $data,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new director signature
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function updateDirectorSignature($Signator_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {

            // checking if Signator_id exists
            $signatureExists = $this->directorSignatureModel->selectDirectorSignatureBYId($Signator_id);
            if (count($signatureExists) == 0) {
                return Errors::notFoundError("Signature not found, please try again?");
            }

            // upload file
            $fileUploadLogo = $this->FileUpload($_FILES, "director_signature_url");
            if (!$fileUploadLogo->success) {
                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => $fileUploadLogo->message,
                ]);
                return $response;
            }

            // update into table
            $file = "director_signature_url";
            $this->directorSignatureModel->updateSignature($Signator_id, $fileUploadLogo->$file);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Signature updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function FileUpload($file, $fileName)
    {
        $results = new \stdClass;
        $target_dir = "public/uploads/";
        $file_name = $target_dir . basename($file[$fileName]["name"]);
        $file_size = $file[$fileName]['size'];
        $file_tmp = $file[$fileName]['tmp_name'];
        $temp = str_replace(array(" ", "-"), "_", basename($file[$fileName]["name"]));
        $newfilename = $target_dir . round(microtime(true)) . '_' . $temp;
        $tmp = explode('.', $file_name);
        $file_ext = end($tmp);

        // Check if file already exists
        if (file_exists($file_name)) {
            $results->message = "Sorry, " . $file[$fileName]["name"] . " file already exists.";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        // Check if file extension
        $extensions = array("jpeg", "jpg", "png", "PNG", "JPEG", "JPG");
        if (in_array($file_ext, $extensions) === false) {
            $results->message = $file[$fileName]["name"] . " extension not allowed, please choose a JPEG, PNG, JPG file.";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        // Check if file size
        if ($file_size > 2097152) {
            $results->message = "Sorry, " . $file[$fileName]["name"] . " File size excessed 2 MB";
            $results->success = false;
            $results->$fileName = $file_name;
            return $results;
        }

        move_uploaded_file($file_tmp, SITE_ROOT . "/" . $newfilename);
        $results->message = $file[$fileName]["name"] . " file uploaded succesfuly";
        $results->success = true;
        $results->$fileName = $newfilename;
        return $results;
    }

}
$controller = new directorSignatureController($this->db, $request_method, $params);
$controller->processRequest();
