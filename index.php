<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type,Authorization');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    header('Content-Type: multipart/form-data');
    die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
define('SITE_ROOT', realpath(dirname(__FILE__)));

require "bootstrap.php";

use Src\Routes\MainRoutes;

//Route instance
$route = new MainRoutes();

//route address and location

// Users routes
$route->router("/user", "src/Controller/userscontroller.php");
$route->router("/user/{id}", "src/Controller/userscontroller.php");
$route->router("/user/list/{page}/{role_id}", "src/Controller/userscontroller.php");
$route->router("/user/assign/role", "src/Controller/userscontroller.php");
$route->router("/user/current/info", "src/Controller/authcontroller.php");
$route->router("/user/account/{action}", "src/Controller/authcontroller.php");
$route->router("/user/account/{action}/{user_id}", "src/Controller/userscontroller.php");
$route->router("/user/updateinfo/{action}/{user_id}", "src/Controller/authcontroller.php");

// District routes
$route->router("/district", "src/Controller/districtscontroller.php");
$route->router("/district/{district_code}", "src/Controller/districtscontroller.php");

// Province routes
$route->router("/qualification", "src/Controller/qualificationscontroller.php");
$route->router("/qualification/{qualification_id}", "src/Controller/qualificationscontroller.php");

// TRAININGS
$route->router("/trainings/{action}", "src/Controller/trainingsController.php");
$route->router("/trainings/{action}/{id}", "src/Controller/trainingsController.php");
$route->router("/trainings/trainees/{training_id}/{cohort_id}", "src/Controller/trainingsController.php");

// COHORTS
$route->router("/cohorts/{action}", "src/Controller/cohortsController.php");
$route->router("/cohorts/{action}/{id}", "src/Controller/cohortsController.php");

// COHORTS Conditions
$route->router("/cohortcondition/{action}", "src/Controller/cohortconditionController.php");
$route->router("/cohortcondition/{action}/{id}", "src/Controller/cohortconditionController.php");

// locations
$route->router("/locations/{action}/{id}", "src/Controller/locationsController.php");

// role
$route->router("/role", "src/Controller/userrolecontroller.php");
$route->router("/role/{action}", "src/Controller/userrolecontroller.php");

//SYSTEM FUNCTION
$route->router("/systemfunction/{action}", "src/Controller/systemFunctionController.php");

//TRAINING CENTER
$route->router("/trainingcenter", "src/Controller/trainingCenterController.php");
$route->router("/trainingcenter/{action}", "src/Controller/trainingCenterController.php");
$route->router("/trainingcenter/{action}/{id}", "src/Controller/trainingCenterController.php");

// TRAINER
$route->router("/trainer", "src/Controller/trainerController.php");
$route->router("/trainer/{action}", "src/Controller/trainerController.php");
$route->router("/trainer/{action}/{training_id}", "src/Controller/trainerController.php");

// ELEARNING
$route->router("/elearning/{action}/{course_id}/{cohort_id}", "src/Controller/elearningController.php");

// TRAINING TYPE
$route->router("/trainingtype", "src/Controller/trainingtypeController.php");
$route->router("/trainingtype/{action}", "src/Controller/trainingtypeController.php");
$route->router("/trainingtype/{action}/{training_type_id}", "src/Controller/trainingtypeController.php");

// UPDATING NEW PASSWORD OR CHANGE PASSWORD
$route->router("/changepassword/{action}", "src/Controller/changePasswordController.php");

//write it at the last
//arg is 404 file location
$route->notFound("404.php");
