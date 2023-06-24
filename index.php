<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type,Authorization');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require "bootstrap.php";

use Src\Routes\MainRoutes;

//Route instance
$route = new MainRoutes();

            //---Joseph Routes---//

//route address and location

// Users routes 
$route->router("/user", "src/Controller/userscontroller.php");
$route->router("/user/{id}","src/Controller/userscontroller.php");
$route->router("/user/assign/role","src/Controller/userscontroller.php");
$route->router("/user/account/{action}", "src/Controller/authcontroller.php");
$route->router("/user/account/{action}/{user_id}", "src/Controller/userscontroller.php");
$route->router("/user/current/info", "src/Controller/authcontroller.php");
$route->router("/user/updateinfo/{action}/{user_id}", "src/Controller/authcontroller.php");

// Roles routes
$route->router("/role", "src/Controller/rolescontroller.php");
$route->router("/role/{id}", "src/Controller/rolescontroller.php");

// District routes
$route->router("/district", "src/Controller/districtscontroller.php");
$route->router("/district/{district_code}", "src/Controller/districtscontroller.php");

// Province routes
$route->router("/qualification", "src/Controller/qualificationscontroller.php");
$route->router("/qualification/{qualification_id}", "src/Controller/qualificationscontroller.php");

// Reb limit set to qualification routes
$route->router("/minicofinlimit", "src/Controller/minecofinlimitscontroller.php");
$route->router("/minicofinlimit/academic/{academic_year_id}", "src/Controller/minecofinlimitscontroller.php");
$route->router("/minicofinlimit", "src/Controller/minecofinlimitscontroller.php");

// Reb limit set to district routes
$route->router("/rebdistribution", "src/Controller/rebdistributionsmodel.php");
$route->router("/rebdistribution/{action}/{academic_year_id}", "src/Controller/rebdistributionsmodel.php");

// District distribution post to school
$route->router("/districtdistribution/{action}", "src/Controller/posts.php");
$route->router("/districtdistribution/{action}/{district_code}/{academic_year_id}", "src/Controller/posts.php");

// Reports routes
$route->router("/report/school/{action}/{school_code}", "src/Controller/reportscontroller.php");
$route->router("/report/district/{action}/{district_code}", "src/Controller/reportscontroller.php");


// Basic info routes
$route->router("/basicinfos/{action}", "src/Controller/basicinfocontroller.php");
$route->router("/basicinfos/{action}/{district_code}", "src/Controller/basicinfocontroller.php");

        //---Clement Routes---//

// AcademicCalendar routes
$route->router("/academiccalendars", "src/Controller/planController.php");
$route->router("/academiccalendar/{id}", "src/Controller/planController.php");

// Positions routes
$route->router("/positions", "src/Controller/positionController.php");
$route->router("/position/{id}", "src/Controller/positionController.php");

// Post Request Reasons
$route->router("/postRequestReasons", "src/Controller/postRequestReasonsController.php");

// Post request
$route->router("/postrequests/{action}/{academicid}/{id}", "src/Controller/postRequestController.php");
$route->router("/postrequest/request/{action}", "src/Controller/postRequestController.php");

// Qualifications
$route->router("/qualifications", "src/Controller/qualificationController.php");

// Suspenion and Termination
$route->router("/changeTeacherStatusController/{action}", "src/Controller/changeTeacherStatusController.php");

// Teacher transfer
$route->router("/teachertransfer/{action}/{id}", "src/Controller/teacherTransferController.php");
$route->router("/teachertransfers/{action}",  "src/Controller/teacherTransferController.php");

// PROFILE
$route->router("/profile/{usertype}/{id}", "src/Controller/profileController.php");

// PLACEMENT REPORT
$route->router("/placementreport/{action}/{level}/{id}", "src/Controller/placementReportController.php");


//write it at the last
//arg is 404 file location
$route->notFound("404.php");

?>