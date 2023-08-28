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

//route address and location

// Users routes
$route->router("/user", "src/Controller/userscontroller.php");
$route->router("/user/{id}", "src/Controller/userscontroller.php");
$route->router("/user/assign/role", "src/Controller/userscontroller.php");
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

// TRAININGS
$route->router("/trainings/{action}", "src/Controller/trainingsController.php");
$route->router("/trainings/{action}/{id}", "src/Controller/trainingsController.php");

// COHORTS
$route->router("/cohorts/{action}", "src/Controller/cohortsController.php");
$route->router("/cohorts/{action}/{id}", "src/Controller/cohortsController.php");

// COHORTS Conditions
$route->router("/cohortcondition/{action}/{id}", "src/Controller/cohortconditionController.php");

// locations
$route->router("/locations/{action}/{id}", "src/Controller/locationsController.php");

//write it at the last
//arg is 404 file location
$route->notFound("404.php");
