<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$route->router("/user/list/{page}/{role_id}/{status}", "src/Controller/userscontroller.php");
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
$route->router("/locations/{action}", "src/Controller/locationsController.php");
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
$route->router("/elearning/{action}", "src/Controller/elearningController.php");
$route->router("/elearning/{action}/{course_id}/{cohort_id}", "src/Controller/elearningController.php");
$route->router("/enrollTeachersToElearning/{courseCode}/{staff_code}", "src/Controller/elearningEnrollmentController.php");

// TRAINING TYPE
$route->router("/trainingtype", "src/Controller/trainingtypeController.php");
$route->router("/trainingtype/{action}", "src/Controller/trainingtypeController.php");
$route->router("/trainingtype/{action}/{training_type_id}", "src/Controller/trainingtypeController.php");

// UPDATING NEW PASSWORD OR CHANGE PASSWORD
$route->router("/changepassword/{action}", "src/Controller/changePasswordController.php");

// TRAINING TYPE
$route->router("/invitation", "src/Controller/invitationLetterController.php");
$route->router("/invitation/{action}", "src/Controller/invitationLetterController.php");
$route->router("/invitation/{action}/{id}", "src/Controller/invitationLetterController.php");

// ASSETS CATEGORY
$route->router("/assetscategory", "src/Controller/assetCategoriesController.php");
$route->router("/assetscategory/{assets_categories_id}", "src/Controller/assetCategoriesController.php");

// ASSETS SUB CATEGORY
$route->router("/assetssubcategory", "src/Controller/assetSubCategoriesController.php");
$route->router("/assetssubcategory/{assets_categories_id}", "src/Controller/assetSubCategoriesController.php");

// ASSETS BRANDS
$route->router("/brands", "src/Controller/brandsController.php");
$route->router("/brands/{id}", "src/Controller/brandsController.php");

// ASSETS
$route->router("/assets", "src/Controller/assetsController.php");
$route->router("/assets/{id}", "src/Controller/assetsController.php");
$route->router("/assets/{action}/{id}", "src/Controller/assetsController.php");

// BATCH CATEGORY DISTRIBUTION
$route->router("/batchdistribution", "src/Controller/assetsDistributionController.php");
$route->router("/batchdistribution/{id}", "src/Controller/assetsDistributionController.php");
$route->router("/batchdistribution/{action}/{id}", "src/Controller/assetsDistributionController.php");

// ICTFOCAL TEACHERS
$route->router("/ictfocalteachers/{action}", "src/Controller/ictfocaltechersController.php");

// BULKENROLL TEACHERS
$route->router("/bulkenroll/{action}", "src/Controller/bulkEnrollController.php");

// SCHOOL LOCATION
$route->router("/schoollocation/{action}", "src/Controller/schoolLocationController.php");

// TEACHER STUDY HIERARCHY
$route->router("/studyhierarchy", "src/Controller/TeacherStudyHierarchyController.php");
$route->router("/studyhierarchy/{action}", "src/Controller/TeacherStudyHierarchyController.php");
$route->router("/studyhierarchy/{action}/{user_id}", "src/Controller/TeacherStudyHierarchyController.php");

// POSITION
$route->router("/positions", "src/Controller/positionscontroller.php");
$route->router("/positions/{position_id}", "src/Controller/positionscontroller.php");

// COP REPORTS
$route->router("/copreports", "src/Controller/copReportsController.php");
$route->router("/copreports/{action}", "src/Controller/copReportsController.php");
$route->router("/copreports/{action}/{user_id}", "src/Controller/copReportsController.php");

// MODULE PROGRESS REPORTS
$route->router("/moduleprogress", "src/Controller/moduleProgressReportsController.php");
$route->router("/moduleprogress/{action}", "src/Controller/moduleProgressReportsController.php");
$route->router("/moduleprogress/{action}/{user_id}", "src/Controller/moduleProgressReportsController.php");

// NOTIFICATION
$route->router("/notification", "src/Controller/notificationController.php");
$route->router("/notification/{type}", "src/Controller/notificationController.php");
$route->router("/notification/{type}/{action}", "src/Controller/notificationController.php");
$route->router("/notification/{type}/{action}/{message_id}", "src/Controller/notificationController.php");

// GENERAL REPORT
$route->router("/generalreport/{action}", "src/Controller/reportController.php");

// TRAINEERS
$route->router("/traineers", "src/Controller/traineersController.php");
$route->router("/traineers/{action}", "src/Controller/traineersController.php");

//write it at the last
//arg is 404 file location
$route->notFound("404.php");
