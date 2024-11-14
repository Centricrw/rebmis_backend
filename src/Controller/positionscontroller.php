<?php
namespace Src\Controller;

use Src\Models\PositionsModel;
use Src\System\DatabaseConnector;
use Src\System\Errors;

class positionsController
{
    private $db;
    private $positionsModel;
    private $request_method;
    private $params;
    private $closeConnection;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->positionsModel = new PositionsModel($db);
        $this->closeConnection = new DatabaseConnector();
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) == 1) {
                    $response = $this->getPosition($this->params['position_id']);
                } else {
                    $response = $this->getPositions();
                }
                break;
            default:
                $response = Errors::notFoundError('plan not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            $this->closeConnection->closeConnection();
            echo $response['body'];
        }
    }

    // Get all positions
    function getPositions()
    {
        $result = $this->positionsModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a position by id
    function getPosition($params)
    {
        $result = $this->positionsModel->findOne($params);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

}
$controller = new positionsController($this->db, $request_method, $params);
$controller->processRequest();
