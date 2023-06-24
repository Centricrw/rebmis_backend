<?php
namespace Src\Controller;

use Src\Models\AuthModel;
use Src\Models\UsersModel;
use Src\Models\RolesModel;
use Src\Models\UserRoleModel;
use Src\Models\DistrictsModel;
use Src\Models\SchoolsModel;
use Src\Models\SchoolLocationsModel;
use Src\Models\SectorsModel;
use Src\Models\StakeholdersModel;
use Src\Models\SchoolLevelModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use stdClass;

  class ReportsController {
  private $db;
  private $usersModel;
  private $authModel;
  private $rolesModel;
  private $userRoleModel;
  private $districtsModel;
  private $schoolsModel;
  private $schoolLocationsModel;
  private $schoolLevelModel;
  private $sectorsModel;
  private $stakeholdersModel;
  private $request_method;
  private $params;

  public function __construct($db,$request_method,$params)
  {
    $this->db = $db;
    $this->request_method = $request_method;
    $this->params = $params;
    $this->authModel = new AuthModel($db);
    $this->usersModel = new UsersModel($db);
    $this->rolesModel = new RolesModel($db);
    $this->userRoleModel = new UserRoleModel($db);
    $this->districtsModel = new DistrictsModel($db);
    $this->schoolsModel = new SchoolsModel($db);
    $this->sectorsModel = new SectorsModel($db);
    $this->stakeholdersModel = new StakeholdersModel($db);
    $this->schoolLocationsModel = new SchoolLocationsModel($db);
    $this->schoolLevelModel = new SchoolLevelModel($db);
  }

  function processRequest()
  {
      switch ($this->request_method) {
          case 'GET':
            if(sizeof($this->params) > 1){
              if($this->params['action'] == "teachers"){
                $response = $this->getSchoolTeachers($this->params['school_code']);
              }elseif($this->params['action'] == "schools"){
                $response = $this->getDistrictSchools($this->params['district_code']);
              }
            }else{
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

  function getSchoolTeachers($school_code){
    $result = $this->userRoleModel->findCurrentSchoolTeachers($school_code);
    $staff = [];
    $roles = [];
    $school_staff = new stdClass();

    foreach ($result as $value) {
      $new_user = new stdClass();
      $user = $this->usersModel->findById($value['user_id'],1);
      $role = $this->rolesModel->findById($value['role_id']);
      // $level = $this->schoolLevelModel->findById($value['school_level_id']);

      $new_user->user_id = sizeof($user) > 0 ? $user[0]['user_id'] : null;
      $new_user->role_id = sizeof($role) > 0 ? $role[0]['role_id'] : null;
      $new_user->role = sizeof($role) > 0 ? $role[0]['role'] :null;
      $new_user->first_name = sizeof($user) > 0 ? $user[0]['first_name'] : null;
      $new_user->last_name = sizeof($user) > 0 ? $user[0]['last_name'] : null;
      $new_user->phone_numbers = sizeof($user) > 0 ? $user[0]['phone_numbers'] : null;
      $new_user->email = sizeof($user) > 0 ? $user[0]['email'] : null;
      $new_user->username = sizeof($user) > 0 ? $user[0]['username'] : null;
      $new_user->status = sizeof($user) > 0 ? $user[0]['status'] : null;

      array_push($staff,$new_user);
      array_push($roles,$role[0]);
    }
    $school_staff->staff = $staff;
    $school_staff->role = $roles;
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($school_staff);
    return $response;
  }

  function getDistrictSchools($district_code){
    $result = $this->schoolLocationsModel->findDistrictSchools($district_code);

    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

}
$controller = new ReportsController($this->db, $request_method,$params);
$controller->processRequest();