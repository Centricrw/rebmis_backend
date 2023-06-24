<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\PostRequestModel;
  use Src\System\AuthValidation;

    class postRequestController {
    private $db;
    private $postRequestModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->postRequestModel = new PostRequestModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // FETCHING DATA
            case 'GET':
              if(sizeof($this->params) == 3 ){
                if($this->params['action'] == 'getschoolrequests'){
                  $response = $this->getSchoolRequests($this->params['academicid'], $this->params['id']);
                }elseif($this->params['action'] == 'getdistrictrequests'){
                  $response = $this->getSchoolRequestsPerDistrict($this->params['academicid'], $this->params['id']);
                }
              }
            break;

            // ADDING OR UPDATING DATA
            case 'POST':
              if(sizeof($this->params) == 1){
                if($this->params['action'] == 'headteacheraddarequest'){
                  $response = $this->headteacheraddarequest();
                }
                elseif($this->params['action'] == 'ddeaddarequest'){
                  $response = $this->ddeaddarequest();
                }
              }
            break;
            case 'DELETE':
              if(sizeof($this->params) == 1){
                $response = $this->deletePostRequest($this->params['id']);
              }
            default:
              $response = Errors::notFoundError('plan not found');
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all headteacher post requests per school
    function getSchoolRequests($academicId, $schoolId)
    {
        $result = $this->postRequestModel->getSchoolRequests($academicId, $schoolId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get all headteacher post requests per district
    function getSchoolRequestsPerDistrict($academicId, $districtId){
        $result = $this->postRequestModel->getSchoolRequestsPerDistrict($academicId, $districtId);
        
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Add A HT REQUEST
    function headteacheraddarequest(){
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

      $data = (array) json_decode(file_get_contents('php://input'), TRUE);
        // Validate input if not empty
        if(empty($data)){
          return Errors::unprocessableEntityResponse();
        }
      foreach($data as $input){

        $result = $this->postRequestModel->addhdrequest($input, $user_id);
          
      }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
      
    }

    // ADD A DEE REQUEST
    function ddeaddarequest(){
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


      $input = (array) json_decode(file_get_contents('php://input'), TRUE);
      // Validate input if not empty
      if(!self::validateddeaddarequestInfo($input)){
        return Errors::unprocessableEntityResponse();
      }
  
      $result = $this->postRequestModel->ddeaddarequest($input, $user_id);
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($input);
      return $response;
    }


    // HD Validation
    private function validateheadteacheraddarequestInfo($input)
    {
        if (empty($input['academic_calendar_id'])) {
            return false;
        }
        if (empty($input['school_code'])) {
            return false;
        }
        if (empty($input['position_code'])) {
          return false;
        }
        if (empty($input['qualification_id'])) {
            return false;
        }
        if (empty($input['head_teacher_id'])) {
            return false;
        }
        if (empty($input['head_teacher_post_request'])) {
          return false;
        }
        if (empty($input['head_teacher_reason_id'])) {
          return false;
        }
        if (empty($input['district_code'])) {
            return false;
        }
        return true;
    }

    // DDE Validation
    private function validateddeaddarequestInfo($input)
    {
      if (empty($input['dde_post_request_comment'])) {
          return false;
      }
      if (empty($input['post_request_id'])) {
          return false;
      }
      if (empty($input['dde_post_request'])) {
          return false;
      }
      return true;
    }



    // Get a postrequest by id 
    function getPostRequest($params)
    {
        $result = $this->postRequestModel->findOne($params);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    // Delete a postRequest
    function deletePostRequest($positionId){
      $plan = $this->postRequestModel->findOne($positionId);
      if (sizeof($plan) == 0) {
          return Errors::notFoundError("Plan not found!");
      }

      $this->postRequestModel->delete($positionId,1);

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode(["message"=>"Account Suspended!"]);
      return $response;
    }
  }
    $controller = new postRequestController($this->db, $request_method,$params);
    $controller->processRequest();
