<?php
namespace Src\Routes;

use Src\System\DatabaseConnector;

class MainRoutes
{
    private $db;
    private $moodleDb;

    public function __construct()
    {
        $this->db = (new DatabaseConnector())->getConnection();
        $this->moodleDb = (new DatabaseConnector())->getMoodleConnection();
    }

    /*
    two parameters.

    route : route address
    file : location of the file to show if route address matched

     */

    public function simpleRoute($route, $file)
    {
        //replacing first and last forward slashes
        //$_REQUEST['uri'] will be empty if req uri is /
        //Request method
        $request_method = $_SERVER['REQUEST_METHOD'];
        $params = [];

        if (!empty($_REQUEST['uri'])) {
            $route = preg_replace("/(^\/)|(\/$)/", "", $route);
            $reqUri = preg_replace("/(^\/)|(\/$)/", "", $_REQUEST['uri']);
        } else {
            $reqUri = "/";
        }

        if ($reqUri == $route) {

            //on match include the file.
            include $file;

            //exit because route address matched.
            exit();
        }

    }

    function router($route, $file)
    {

        //will store all the parameters value in this array
        $params = [];

        //will store all the parameters names in this array
        $paramKey = [];

        //Request method
        $request_method = $_SERVER['REQUEST_METHOD'];

        //finding if there is any {?} parameter in $route
        preg_match_all("/(?<={).+?(?=})/", $route, $paramMatches);

        //if the route does not contain any param call simpleRoute();
        if (empty($paramMatches[0])) {
            $this->simpleRoute($route, $file);
            return;
        }

        //setting parameters names
        foreach ($paramMatches[0] as $key) {
            $paramKey[] = $key;
        }

        //replacing first and last forward slashes
        //$_REQUEST['uri'] will be empty if req uri is /
        if (!empty($_REQUEST['uri'])) {
            $route = preg_replace("/(^\/)|(\/$)/", "", $route);
            $reqUri = preg_replace("/(^\/)|(\/$)/", "", $_REQUEST['uri']);
        } else {
            $reqUri = "/";
        }

        //exploding route address
        $uri = explode("/", $route);

        //will store index number where {?} parameter is required in the $route
        $indexNum = [];

        //storing index number, where {?} parameter is required with the help of regex
        foreach ($uri as $index => $param) {
            if (preg_match("/{.*}/", $param)) {
                $indexNum[] = $index;
            }
        }
        //exploding request uri string to array to get
        //the exact index number value of parameter from $_REQUEST['uri']
        $reqUri = explode("/", $reqUri);

        //running for each loop to set the exact index number with reg expression
        //this will help in matching route
        foreach ($indexNum as $key => $index) {

            //in case if req uri with param index is empty then return
            //because url is not valid for this route
            if (empty($reqUri[$index])) {
                return;
            }

            //setting params with params names
            $params[$paramKey[$key]] = $reqUri[$index];
            // Get Params
            //this is to create a regex for comparing route address
            $reqUri[$index] = "{.*}";
        }
        //converting array to sting
        $reqUri = implode("/", $reqUri);

        //replace all / with \/ for reg expression
        //regex to match route is ready !
        $reqUri = str_replace("/", '\\/', $reqUri);

        //now matching route with regex
        if (preg_match("/$reqUri/", $route)) {
            include $file;
            exit();
        }
    }

    public function notFound($file)
    {
        include $file;
        exit();
    }
}
