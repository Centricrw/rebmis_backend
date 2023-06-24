<?php
namespace Src\Controller;
use Src\Models\QualificationsModel;
use Src\System\Errors;
use Src\System\AuthValidation;


class QualificationsController {
  private $db;
  private $qualificationsModel;
  private $request_method;
  private $params;

  public function __construct($db,$request_method,$params)
  {
    $this->db = $db;
    $this->request_method = $request_method;
    $this->params = $params;
    $this->qualificationsModel = new QualificationsModel($db);
  }

  function processRequest()
  {
      switch ($this->request_method) {
          case 'GET':
            if(sizeof($this->params) == 1){
              $response = $this->getOneQualification($this->params['qualification_id']);
            }else{
              $response = $this->getAllQualifications();
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
  // Get all users
  function getAllQualifications()
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
      $result = $this->qualificationsModel->findAll();

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
  }
  // Get one user
  function getOneQualification($qualification_id)
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

      $result = $this->qualificationsModel->findById($qualification_id,0);
      if(sizeof($result) > 0){
      $result = $result[0];
      }else{
          $result = null;
      }
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;

  }
}
  $controller = new QualificationsController($this->db, $request_method,$params);
  $controller->processRequest();
?>