<?php
    namespace Src\Controller;

    use Src\Models\RolesModel;
    use Src\System\Errors;
    use Src\System\AuthValidation;
    
    class RolesController {
    private $db;
    private $rolesModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->rolesModel = new RolesModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
              if(sizeof($this->params) == 1){
                $response = $this->getRoleById($this->params['id']);
              }else{
                $response = $this->getAllRoles();
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

    // Get all roles
    function getAllRoles()
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
        $result = $this->rolesModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a role by id 
    function getRoleById($role_id)
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
        $result = $this->rolesModel->findById($role_id);
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
    $controller = new RolesController($this->db, $request_method,$params);
    $controller->processRequest();
