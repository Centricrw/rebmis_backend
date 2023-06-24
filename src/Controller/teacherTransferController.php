<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\TeacherTransferModel;
  use Src\System\AuthValidation;

    class teacherTransferController {
    private $db;
    private $teacherTransferModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->teacherTransferModel = new TeacherTransferModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == 'getschoolsperdistrict'){
                  $response = $this->getSchoolsPerDistrict($this->params['id']);
                }
                elseif($this->params['action'] == 'teacher'){
                  $response = $this->getTeacherTreansferRequest();
                }
                elseif($this->params['action'] == 'requesteddde'){
                  $response = $this->getTeacherTreansferRequestFoRequestedDde();
                }
                elseif($this->params['action'] == 'outgoingdde'){
                  $response = $this->getTeacherTreansferRequestFoOutgoingDde();
                }
              }
            break;

            // INSERT DATA
            case 'POST':
              $response = $this->teacherRequestATransfer();
            break;

            // UPDATE DATA
            case 'PATCH':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == 'incoming'){
                  $response = $this->incomingDdeTransferDecision();
                }
                elseif($this->params['action'] == 'outgoing'){
                  $response = $this->outgoingDdeTransferDecision();
                }
                elseif($this->params['action'] == 'leaving'){
                  $response = $this->teacherLeaving();
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

    function getTeacherTreansferRequest(){
      $user_id = $this->getUserId();
      
      $result = $this->teacherTransferModel->getTeacherTreansferRequest($user_id);
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    function getTeacherTreansferRequestFoRequestedDde(){
      $user_id = $this->getUserId();
      
      $result = $this->teacherTransferModel->getTeacherTreansferRequestForRequestedDde($user_id);
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }


    function getTeacherTreansferRequestFoOutgoingDde(){
      $user_id = $this->getUserId();
      
      $result = $this->teacherTransferModel->getTeacherTreansferRequestForOutgoingDde($user_id);
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    function getSchoolsPerDistrict($districtId){
        
        // CALL MODELS TO REQUEST TO ERMINATE
        $result = $this->teacherTransferModel->getSchoolsDataPerDistrict($districtId);
    
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    function teacherRequestATransfer(){
      $user_id = $this->getUserId();

      $data = (array) json_decode(file_get_contents('php://input'), TRUE);

         // Validate input if not empty
        if(!self::validateTeacherTransferRequestInfo($data)){
              return Errors::unprocessableEntityResponse();
        }
        // CALL MODELS TO REQUEST TO A TRANSFER
          $result = $this->teacherTransferModel->teacherRequestATransfer($data, $user_id);
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    function incomingDdeTransferDecision(){
      $user_id = $this->getUserId();

      $data = (array) json_decode(file_get_contents('php://input'), TRUE);

      // CHECK IF ITS AN APPROVAL OR REJECTION
      if($data['requested_status']== 'APPROVED'){
         // Validate input if not empty
        if(!self::validateIncomingDdeApproveDecisionInfo($data)){
              return Errors::unprocessableEntityResponse();
        }
      }elseif($data['requested_status']== 'REJECTED')
      {
        if(!self::validateIncomingDdeRejectDecisionInfo($data)){
          return Errors::unprocessableEntityResponse();
        }
      }
        // CALL MODELS TO REQUEST TO A TRANSFER
          $result = $this->teacherTransferModel->incomingDdeTransferDecision($data, $user_id);
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    function outgoingDdeTransferDecision(){
      $user_id = $this->getUserId();

      $data = (array) json_decode(file_get_contents('php://input'), TRUE);

      // CHECK IF ITS AN APPROVAL OR REJECTION
      // Validate input if not empty
      if(!self::validateOutgoingDdeApproveDecisionInfo($data)){
            return Errors::unprocessableEntityResponse();
      }
      // CALL MODELS TO REQUEST TO A TRANSFER
        $result = $this->teacherTransferModel->outgoingDdeTransferDecision($data, $user_id);
      
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    function teacherLeaving(){
      $data = (array) json_decode(file_get_contents('php://input'), TRUE);

      // CHECK IF ITS AN APPROVAL OR REJECTION
      // Validate input if not empty
      if(!self::validateTeacherLeaving($data)){
            return Errors::unprocessableEntityResponse();
      }
      // CALL MODELS TO REQUEST TO A TRANSFER
        $result = $this->teacherTransferModel->teacherLeaving($data['teacher_transfer_id']);
      
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function validateTeacherTransferRequestInfo($input)
    {
      if (empty($input['school_from_id'])) {
        return false;
      }
      if (empty($input['requested_school_id'])) {
        return false;
      }
      if (empty($input['teacher_reason'])) {
          return false;
      }
      if (empty($input['teacher_supporting_document'])) {
          return false;
      }
      return true;
    }

    private function validateIncomingDdeApproveDecisionInfo($input)
    {
      if (empty($input['teacher_transfer_id'])) {
        return false;
      }
      if (empty($input['requested_status'])) {
        return false;
      }
      if (empty($input['approved_school_id'])) {
          return false;
      }
      if (empty($input['requested_comment'])) {
          return false;
      }
      return true;
    }

    private function validateIncomingDdeRejectDecisionInfo($input)
    {
      if (empty($input['teacher_transfer_id'])) {
        return false;
      }
      if (empty($input['requested_status'])) {
        return false;
      }
      return true;
    }

    private function validateOutgoingDdeApproveDecisionInfo($input)
    {
     
      if (empty($input['teacher_transfer_id'])) {
        return false;
      }
      if (empty($input['outgoing_status'])) {
        return false;
      }
      if (empty($input['outgoing_dde_comment'])) {
        return false;
      }

      return true;
    }

    private function getUserId(){
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

      return AuthValidation::decodedData($jwt_data)->data->id;
    }

    function validateTeacherLeaving($input){
      if (empty($input['teacher_transfer_id'])) {
        return false;
      }
      return true;
    }
  }
    $controller = new teacherTransferController($this->db, $request_method,$params);
    $controller->processRequest();
