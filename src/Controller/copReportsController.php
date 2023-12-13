<?php
namespace Src\Controller;

use DateTime;
use Src\Models\CohortsModel;
use Src\Models\CopReportsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;

class CopReportsController
{
    private $db;
    private $copReportsModel;
    private $cohortsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->copReportsModel = new CopReportsModel($db);
        $this->cohortsModel = new CohortsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->createNewCopReports();
                break;
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "details") {
                    $response = $this->createNewCopReportsDetails();
                } else {
                    $response = $this->createNewCopReports();
                }
                break;
            case "PUT":
                $response = $this->createNewCopReports();
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

    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Validate copreports details
     * @param array $element
     * @throws InvalidDataException
     */
    function validatingCopReportsDetails(array $element)
    {
        // Validate cop_report_id
        if (!isset($element["cop_report_id"]) || empty($element["cop_report_id"])) {
            throw new InvalidDataException("On cop_report_id is either not set or empty");
        }

        if (isset($element["cop_report_id"])) {
            $trainingExists = $this->copReportsModel->getCopReportsByID($element["cop_report_id"]);
            if (sizeof($trainingExists) == 0) {
                throw new InvalidDataException("On cop_report_id not found, please try again?");
            }
        }

        // Validate cop_report_details_title
        if (!isset($element["cop_report_details_title"]) || empty($element["cop_report_details_title"])) {
            throw new InvalidDataException("On cop_report_details_title is either not set or empty");
        }

        // Validate start_date
        if (!isset($element["start_date"]) || !$this->validateDate($element["start_date"])) {
            throw new InvalidDataException("Invalid start date format must be 'YYYY-MM-DD'");
        }

        // Validate end_date
        if (!isset($element["end_date"]) || !$this->validateDate($element["end_date"])) {
            throw new InvalidDataException("Invalid end date format must be 'YYYY-MM-DD'");
        }
    }

    /**
     * Validate copreports
     * @param array $item
     * @throws InvalidDataException
     */

    function inputDataValidationCopReports(array $item)
    {
        // Validate cohortId
        if (!isset($item["cohortId"]) || empty($item["cohortId"])) {
            throw new InvalidDataException("On cohortId is either not set or empty");
        }

        if (isset($item["cohortId"])) {
            $trainingExists = $this->cohortsModel->getOneCohort($item["cohortId"]);
            if (sizeof($trainingExists) == 0) {
                throw new InvalidDataException("On cohortId not found, please try again?");
            }
        }

        // Validate cop_report_title
        if (!isset($item["cop_report_title"]) || empty($item["cop_report_title"])) {
            throw new InvalidDataException("On cop_report_title is either not set or empty");
        }
    }

    /**
     * Create new cop reports
     * @param VOID
     * @return OBJECT $results
     */

    public function createNewCopReports()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // validation
            $this->inputDataValidationCopReports($inputData);

            // check if title exists
            $copReportTitleExists = $this->copReportsModel->getCopReportsByTitle($inputData);
            if (sizeof($copReportTitleExists) > 0) {
                return Errors::existError("Cop report title allready exists!, please try again?");
            }

            // create new cop
            $inputData['cop_report_id'] = UuidGenerator::gUuid();
            $inputData['created_by'] = $logged_user_id;
            $result = $this->copReportsModel->createNewCopReport($inputData);

            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;

        } catch (InvalidDataException $e) {
            return Errors::unprocessableEntityResponse($e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new cop reports details
     * @param VOID
     * @return OBJECT $results
     */

    public function createNewCopReportsDetails()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // validation
            $this->validatingCopReportsDetails($inputData);

            // check if title exists
            $copReportTitleExists = $this->copReportsModel->getCopReportsDetailsByTitle($inputData);
            if (sizeof($copReportTitleExists) > 0) {
                return Errors::existError("Title allready exists!, please try again?");
            }

            // create new cop details
            $inputData['cop_report_details_id'] = UuidGenerator::gUuid();
            $inputData['created_by'] = $logged_user_id;
            $result = $this->copReportsModel->createNewCopReportDetails($inputData);

            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;

        } catch (InvalidDataException $e) {
            return Errors::unprocessableEntityResponse($e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}

$controller = new CopReportsController($this->db, $request_method, $params);
$controller->processRequest();
