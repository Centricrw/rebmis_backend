<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\PlanModel;
  use Src\System\AuthValidation;

    class CalendarController {
    private $db;
    private $planModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->planModel = new PlanModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            
            case 'POST':
              $response = $this->addPlan();
              break;
            case 'DELETE':
              if(sizeof($this->params) == 1){
                $response = $this->deletePlan($this->params['id']);
              }
              break;

              case 'GET':
                if(sizeof($this->params) == 1){
                  $response = $this->getCalendar($this->params['id']);
                }else{
                  $response = $this->getCalendars();
                }
                break;
            default:
              $response = Errors::notFoundError('plan not found');
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all calendars
    function getCalendars()
    {
        // $result = ["id"=> $params["id"]]; 
        $result = $this->planModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a calendar by id 
    function getCalendar($params)
    {
        $result = $this->planModel->findOne($params);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Add a plan
    function addPlan(){
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
      if(!self::validatePlanInfo($input)){
        return Errors::unprocessableEntityResponse();
      }
      
      $result = $this->planModel->insert($input, $user_id);
      //$userAuthData = $this->authModel->findOne($input['username']);
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($input);
      return $response;
    }

    // Delete a plan
    function deletePlan($planId){
      $plan = $this->planModel->findOne($planId);
      if (sizeof($plan) == 0) {
          return Errors::notFoundError("Plan not found!");
      }

      $this->planModel->delete($planId,1);

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode(["message"=>"Account Suspended!"]);
      return $response;
    }



    private function validatePlanInfo($input)
    {

        if (empty($input['academic_year_name'])) {
            return false;
        }
        if (empty($input['academic_year_description'])) {
            return false;
        }
        if (empty($input['academic_year_start'])) {
          return false;
        }
        if (empty($input['academic_year_end'])) {
          return false;
        }
        
        if (empty($input['post_request_start'])) {
          return false;
        }
        if (empty($input['post_request_end'])) {
            return false;
        }
        if (empty($input['transfer_request_start'])) {
          return false;
        }
        if (empty($input['transfer_request_end'])) {
            return false;
        }
        if (empty($input['internal_transfer_assessment_start'])) {
          return false;
        }
        if (empty($input['internal_transfer_assessment_end'])) {
          return false;
        }
        if (empty($input['external_transfer_assessment_start'])) {
          return false;
        }
        if (empty($input['external_transfer_assessment_end'])) {
            return false;
        }
        if (empty($input['teacher_recruitment_start'])) {
          return false;
        }
        if (empty($input['teacher_recruitment_end'])) {
          return false;
        }
        return true;
    }
  }
    $controller = new CalendarController($this->db, $request_method,$params);
    $controller->processRequest();
