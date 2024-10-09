<?php
namespace Src\Controller;

use Src\Models\LivemoodlegradeModle;
use Src\System\Errors;

class livemoodlegradeControllers
{
    private $db;
    private $livemoodlegradeModle;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->livemoodlegradeModle = new LivemoodlegradeModle($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "get_grades") {
                    $response = $this->get_grades();}
            default:
                $response = Errors::notFoundError("no request provided");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function get_grades()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->reportModel->get_grades($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new livemoodlegradeControllers($this->db, $request_method, $params);
$controller->processRequest();
