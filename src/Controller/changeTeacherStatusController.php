<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\ChangeTeacherStatusModle;
  use Src\System\AuthValidation;

    class changeTeacherStatusController {
    private $db;
    private $changeTeacherStatusModle;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->changeTeacherStatusModle = new ChangeTeacherStatusModle($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // INSERT Data
            case 'POST':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == 'requestchangeteacherstatus'){
                  $response = $this->requestChangeTeacherStatus();
                }
              }
            break;

            // UPDATE DATA
            case 'PATCH':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == 'hrchangeteacherstatus'){
                  $response = $this->hrChangeTeacherStatus();
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

    //HD REQUEST TO SUSPEND OR TERMINATE A TEACHER
    function requestChangeTeacherStatus()
    {
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

      // CHECK IF ITS A SUSPENSION OR TERMINATION
      if($data['changeStatusType'] == 'PENDING SUSPENTION'){
          // Validate input if not empty
          if(!self::validatesuspendteacherInfo($data)){
            return Errors::unprocessableEntityResponse();
          }
          // CALL MODELS TO REQUEST TO SUSPEND
          $result = $this->changeTeacherStatusModle->changeTeacherStatusRequest($data, $user_id);
      }
      elseif($data['changeStatusType'] == 'PENDING TERMINATION'){
          // Validate input if not empty
          if(!self::validateterminateteacherInfo($data)){
            return Errors::unprocessableEntityResponse();
          }
          // CALL MODELS TO REQUEST TO ERMINATE
          $result = $this->changeTeacherStatusModle->changeTeacherStatusRequest($data, $user_id);
      }
        
     
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    // Validate Teacher Suspension Request
    private function validatesuspendteacherInfo($input)
    {
        if (empty($input['staff_to_changestatus_id'])) {
            return false;
        }
        if (empty($input['requested_by_reason_id'])) {
          return false;
        }
        if (empty($input['requested_by_comment'])) {
            return false;
        }
        if (empty($input['requested_to_suspend_from'])) {
            return false;
        }
        if (empty($input['requested_to_suspend_to'])) {
            return false;
        }
        return true;
    }

    // Validate Teacher Suspension Request
    private function validateterminateteacherInfo($input)
    {
        if (empty($input['staff_to_changestatus_id'])) {
            return false;
        }
        if (empty($input['requested_by_reason_id'])) {
          return false;
        }
        if (empty($input['requested_by_comment'])) {
            return false;
        }
        return true;
    }

    function hrChangeTeacherStatus()
    {
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

      // CHECK IF ITS AN HR SUSPENSION OR TERMINATION
      if($data['decision'] == 'SUSPENDED'){
          // Validate input if not empty
          if(!self::validatehrsuspendteacherInfo($data)){
            return Errors::unprocessableEntityResponse();
          }
          // CALL MODELS TO REQUEST TO SUSPEND
          $result = $this->changeTeacherStatusModle->changeTeacherStatusDecision($data, $user_id);
      }
      elseif($data['decision'] == 'TERMINATED'){
          // Validate input if not empty
          if(!self::validatehrterminateteacherInfo($data)){
            return Errors::unprocessableEntityResponse();
          }
          // CALL MODELS TO REQUEST TO ERMINATE
          $result = $this->changeTeacherStatusModle->changeTeacherStatusDecision($data, $user_id);
      }elseif($data['decision'] == 'REJECTED'){
        $result = $this->changeTeacherStatusModle->changeTeacherStatusDecision($data, $user_id);
      }
        
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    // Validate HR Teacher Suspension DECISION
    private function validatehrsuspendteacherInfo($input)
    {
        if (empty($input['change_staff_status_id'])) {
            return false;
        }
        if (empty($input['decision'])) {
            return false;
        }
        if (empty($input['decided_to_suspend_from'])) {
            return false;
        }
        if (empty($input['decided_to_suspend_to'])) {
            return false;
        }
        if (empty($input['decided_by_comment'])) {
            return false;
        }
        return true;
    }

    // Validate HR Teacher Suspension DECISION
    private function validatehrterminateteacherInfo($input)
    {
        if (empty($input['change_staff_status_id'])) {
            return false;
        }
        if (empty($input['decision'])) {
            return false;
        }
        if (empty($input['decided_by_comment'])) {
            return false;
        }
        return true;
    }

    
  }
    $controller = new changeTeacherStatusController($this->db, $request_method,$params);
    $controller->processRequest();
