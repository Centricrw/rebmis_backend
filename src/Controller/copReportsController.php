<?php
namespace Src\Controller;

use DateTime;
use Src\Models\CohortsModel;
use Src\Models\CopReportsModel;
use Src\Models\LocationsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;

class CopReportsController
{
    private $db;
    private $copReportsModel;
    private $cohortsModel;
    private $locationsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->copReportsModel = new CopReportsModel($db);
        $this->cohortsModel = new CohortsModel($db);
        $this->locationsModel = new LocationsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "cohort") {
                    $response = $this->getAllCopReportsByCohorts($this->params['user_id']);
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "reports") {
                    $response = $this->getAllReportsByDetailes($this->params['user_id']);
                } else {
                    $response = $this->getAllCopReports();
                }
                break;
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "details") {
                    $response = $this->createNewCopReportsDetails();
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "reports") {
                    $response = $this->createNewCopReportsDetailsReports();
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

    /**
     * Validate copreports details reports
     * @param array $element
     * @throws InvalidDataException
     */
    function validatingCopReportsDetailsReports(array $element)
    {
        // Validate cop_report_details_id
        if (!isset($element["cop_report_details_id"]) || empty($element["cop_report_details_id"])) {
            throw new InvalidDataException("On cop_report_details_id is either not set or empty");
        }

        if (isset($element["cop_report_details_id"])) {
            $trainingExists = $this->copReportsModel->getCopReportsDetailsByID($element["cop_report_details_id"]);
            if (sizeof($trainingExists) == 0) {
                throw new InvalidDataException("On cop_report_details_id not found, please try again?");
            }
        }

        // Validate meeting_date
        if (!isset($element["meeting_date"]) || !$this->validateDate($element["meeting_date"])) {
            throw new InvalidDataException("Invalid meeting_date format must be 'YYYY-MM-DD'");
        }

        // Validate next_meeting_date
        if (isset($element["next_meeting_date"]) && !$this->validateDate($element["next_meeting_date"])) {
            throw new InvalidDataException("Invalid next_meeting_date format must be 'YYYY-MM-DD'");
        }

        // Validate next_meeting_superviser
        if (isset($element["next_meeting_superviser"]) && empty($element["next_meeting_superviser"])) {
            throw new InvalidDataException("On next_meeting_superviser is Required!");
        }

        // validate meeting_attendance
        if (!isset($element["meeting_attendance"]) || !is_array($element["meeting_attendance"])) {
            throw new InvalidDataException("On meeting_attendance is Required!");
        }

        if (isset($element["meeting_attendance"])) {
            foreach ($element["meeting_attendance"] as $key => $item) {
                // Validate gender
                if (!isset($item["gender"]) || !in_array($item["gender"], ['Male', 'Female'])) {
                    throw new InvalidDataException("On Index '$key' Gender must be 'Male' or 'Female'");
                }
                // Validate email
                if (!isset($item["email"]) || !is_string($item["email"]) || !filter_var($item["email"], FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidDataException("On index '$key' Email is not validated");
                }
                // Validate full_name
                if (!isset($item["full_name"]) || !is_string($item["full_name"]) || strlen($item["full_name"]) < 2) {
                    throw new InvalidDataException("On index '$key' full_name must be a string with a minimum of 2 characters");
                }
                // Validate nid
                if (!isset($item["nid"]) || !is_string($item["nid"]) || strlen($item["nid"]) != 16) {
                    throw new InvalidDataException("On index '$key' NID must be a string with a maximum of 16 characters");
                }
                // Validate phone number
                if (!isset($item["phone_number"]) || !is_string($item["phone_number"]) || strlen($item["phone_number"]) != 10 || !preg_match('/^07/', $item["phone_number"])) {
                    throw new InvalidDataException("On index '$key' Phone number must be a string starting with '07' and have 10 digits");
                }
            }
        }

        // Validate other keys
        $requiredKeys = ['district_code', 'sector_code', 'school_code', 'course', 'course_summary', 'meeting_benefits', 'meeting_drawback', 'meeting_strategy', 'drawback_to_submit_at_school', 'meeting_supervisor', 'meeting_supervisor_occupation'];

        foreach ($requiredKeys as $requiredKey) {
            if (!isset($element[$requiredKey]) || empty($element[$requiredKey])) {
                throw new InvalidDataException("On $requiredKey is either not set or empty");
            }
        }
    }

    /**
     * Create new cop reports details
     * @param VOID
     * @return OBJECT $results
     */

    public function createNewCopReportsDetailsReports()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // validation
            $this->validatingCopReportsDetailsReports($inputData);

            // get school details
            $school = $this->locationsModel->getAddressDetails($inputData['school_code'], "schools");
            if (sizeof($school) == 0) {
                return Errors::existError("Schools code not found!, please try again?");
            }

            // get district details
            $district = $this->locationsModel->getAddressDetails($inputData['district_code'], "districts");
            if (sizeof($district) == 0) {
                return Errors::existError("District code not found!, please try again?");
            }

            // get sector details
            $sector = $this->locationsModel->getAddressDetails($inputData['sector_code'], "sectors");
            if (sizeof($sector) == 0) {
                return Errors::existError("Sector code not found!, please try again?");
            }

            // checking ischool already submitted
            $copReportExists = $this->copReportsModel->getCopReportsDetailsReportBySchool($inputData);
            if (sizeof($copReportExists) > 0) {
                return Errors::existError("School report allready exists!, please try again?");
            }

            // create new cop details report
            $inputData['report_id'] = UuidGenerator::gUuid();
            $inputData['created_by'] = $logged_user_id;
            $inputData['district_name'] = $district[0]['namedistrict'];
            $inputData['sector_name'] = $sector[0]['namesector'];
            $inputData['school_name'] = $school[0]['school_name'];
            $result = $this->copReportsModel->createNewCopReportDetailsReports($inputData);

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
     * get cop reports
     * @param VOID
     * @return OBJECT $results
     */

    public function getAllCopReports()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $copReports = $this->copReportsModel->getAllCopReports();
            if (sizeof($copReports) > 0) {
                foreach ($copReports as $key => $item) {
                    $copReportsDetails = $this->copReportsModel->getAllCopReportsDetailsByCopReportId($item["cop_report_id"]);
                    $copReports[$key]["cop_reports_details"] = $copReportsDetails;
                }
            }

            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($copReports);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get cop reports by cohort
     * @param STRING $cohortId
     * @return OBJECT $results
     */

    public function getAllCopReportsByCohorts($cohortId)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $copReports = $this->copReportsModel->getAllCopReportsByCohortId($cohortId);
            if (sizeof($copReports) > 0) {
                foreach ($copReports as $key => $item) {
                    $copReportsDetails = $this->copReportsModel->getAllCopReportsDetailsByCopReportId($item["cop_report_id"]);
                    $copReports[$key]["cop_reports_details"] = $copReportsDetails;
                }
            }

            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($copReports);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get reports
     * @param STRING $copReportDetailsId
     * @return OBJECT $results
     */

    public function getAllReportsByDetailes($copReportDetailsId)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $reports = $this->copReportsModel->getAllReportsBYCopReportDetailsId($copReportDetailsId);
            if (sizeof($reports) > 0) {
                foreach ($reports as $key => $item) {
                    $reports[$key]["meeting_attendance"] = json_decode($item['meeting_attendance'], true);
                }
            }
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($reports);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}

$controller = new CopReportsController($this->db, $request_method, $params);
$controller->processRequest();
