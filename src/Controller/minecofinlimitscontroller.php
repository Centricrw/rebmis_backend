<?php
namespace Src\Controller;

use Src\Models\MinecofinLimitsModel;
use Src\System\Errors;
use Src\System\AuthValidation;



  class MinecofinLimitsController {
    private $db;
    private $minecofinLimitsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->minecofinLimitsModel = new MinecofinLimitsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if(sizeof($this->params) == 1){
                $response = $this->getAcademicYearAllocatedPosts($this->params['academic_year_id']);
                }else{
                $response = $this->getAllocatedPosts();
                }
                break;
                case 'POST':
                    $response = $this->createAllocatedPost();
                break;
                case 'PATCH':
                    $response = $this->updateAllocatedPost();
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

    function createAllocatedPost(){
        
        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if(isset($all_headers['Authorization'])){
        $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if(empty($jwt_data->jwt)){
            return Errors::notAuthorized();
        }

        if(!AuthValidation::isValidJwt($jwt_data)){
            return Errors::notAuthorized();
        }
        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        if (sizeof($data) == 0) {
            return Errors::unprocessableEntityResponse();
        }
        $exists = [];
        foreach ($data as $value) {
            if(!empty($value)){
               $is_exist = $this->minecofinLimitsModel->findLimitByAcademicYearQualification($value['academic_year_id'],$value['qualification_id']);
                if(sizeof($is_exist) > 0){
                    $this->minecofinLimitsModel->update($value,$user_id);
                }else{
                    $this->minecofinLimitsModel->insert($value,$user_id);
                }
            }
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(["message"=>"Created"]);
        return $response;
    }
    function updateAllocatedPost(){
        
        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if(isset($all_headers['Authorization'])){
        $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if(empty($jwt_data->jwt)){
            return Errors::notAuthorized();
        }

        if(!AuthValidation::isValidJwt($jwt_data)){
            return Errors::notAuthorized();
        }
        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        if (sizeof($data) == 0) {
            return Errors::unprocessableEntityResponse();
        }
        
        foreach ($data as $value) {
            if(!empty($value)){
                $this->minecofinLimitsModel->update($value,$user_id);
            }
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(["message"=>"Updated"]);
        return $response;
    }
    function getAllocatedPosts(){
        
            $jwt_data = new \stdClass();

            $all_headers = getallheaders();
            if(isset($all_headers['Authorization'])){
              $jwt_data->jwt = $all_headers['Authorization'];
            }
            // Decoding jwt
            if(empty($jwt_data->jwt)){
              return Errors::notAuthorized();
            }
            if(!AuthValidation::isValidJwt($jwt_data)){
              return Errors::notAuthorized();
            }
            $result = $this->minecofinLimitsModel->findAll();
        
            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($result);
            return $response;
    }
    function getAcademicYearAllocatedPosts($academic_year_id){
            $jwt_data = new \stdClass();

            $all_headers = getallheaders();
            if(isset($all_headers['Authorization'])){
              $jwt_data->jwt = $all_headers['Authorization'];
            }
            // Decoding jwt
            if(empty($jwt_data->jwt)){
              return Errors::notAuthorized();
            }
            if(!AuthValidation::isValidJwt($jwt_data)){
              return Errors::notAuthorized();
            }
            $result = $this->minecofinLimitsModel->findAcademicYearLimits($academic_year_id);
     

            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($result);
            return $response;
    }
}
$controller = new MinecofinLimitsController($this->db, $request_method,$params);
$controller->processRequest();
?>