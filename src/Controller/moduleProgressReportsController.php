<?php
namespace Src\Controller;

use Src\Models\ModuleProgressReportsModel;
use Src\System\DatabaseConnector;
use Src\System\Errors;
use Src\System\UuidGenerator;

class ModuleProgressReportsController
{
    private $db;
    private $moduleProgressReportsModel;
    private $request_method;
    private $params;
    private $closeConnection;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->moduleProgressReportsModel = new ModuleProgressReportsModel($db);
        $this->closeConnection = new DatabaseConnector();
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "one") {
                    $response = $this->getModuleProgressReportById($this->params['user_id']);
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "staff") {
                    $response = $this->getModuleProgressReportByStaffCode($this->params['user_id']);
                } else {
                    $response = $this->getAllModuleProgressReport();
                }
                break;
            case 'POST':
                $response = $this->createNewModuleProgressReport();
                break;
            default:
                $response = Errors::notFoundError('Module progress report route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            $this->closeConnection->closeConnection();
            echo $response['body'];
        }
    }

    // Create new module progress report
    function createNewModuleProgressReport()
    {
        try {
            // Get input data
            $data = json_decode(file_get_contents('php://input'), true);
            $newResults = [];
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $value['module'] = isset($value['module']) && is_array($value['module']) ? json_encode($value['module']) : null;
                    // Generate module progress report id
                    $generated_id = UuidGenerator::gUuid();
                    $value['module_progress_reports_id'] = $generated_id;
                    // insert
                    $result = $this->moduleProgressReportsModel->insertNewModuleProgressReports($value);
                    if ($result) {
                        array_push($newResults, $value);
                    }
                }
            }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all module progress report
     * @return OBJECT $response
     */
    public function getAllModuleProgressReport()
    {
        try {
            $results = $this->moduleProgressReportsModel->selectAllModuleProgressReports();
            $newResults = [];
            if (sizeof($results) > 0) {
                foreach ($results as $key => $value) {
                    $value['module'] = isset($value['module']) ? json_decode($value['module']) : null;
                    array_push($newResults, $value);
                }
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting module progress report by id
     * @param STRING $moduleProgressReportsId
     * @return OBJECT $response
     */
    public function getModuleProgressReportById($moduleProgressReportsId)
    {
        try {
            $results = $this->moduleProgressReportsModel->selectModuleProgressReportsById($moduleProgressReportsId);
            $newResults = [];
            if (sizeof($results) > 0) {
                foreach ($results as $key => $value) {
                    $value['module'] = isset($value['module']) ? json_decode($value['module']) : null;
                    array_push($newResults, $value);
                }
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting module progress report by staff code
     * @param STRING $staffCode
     * @return OBJECT $response
     */
    public function getModuleProgressReportByStaffCode($staffCode)
    {
        try {
            $results = $this->moduleProgressReportsModel->selectModuleProgressReportsByStaffCode($staffCode);
            $newResults = [];
            if (sizeof($results) > 0) {
                foreach ($results as $key => $value) {
                    $value['module'] = isset($value['module']) ? json_decode($value['module']) : null;
                    array_push($newResults, $value);
                }
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new ModuleProgressReportsController($this->db, $request_method, $params);
$controller->processRequest();
