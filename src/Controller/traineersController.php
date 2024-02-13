<?php
namespace Src\Controller;

use setasign\Fpdi\Tcpdf\Fpdi;
use Src\Models\CohortsModel;
use Src\Models\TraineersModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class TraineersController
{
    private $db;
    private $traineersModel;
    private $request_method;
    private $userRoleModel;
    private $cohortsModel;
    private $params;
    private $homeDir;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->traineersModel = new TraineersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->cohortsModel = new CohortsModel($db);
        $this->homeDir = dirname(__DIR__, 2);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "certificate") {
                    $response = $this->generateTraineesCertificate($this->params['user_id']);
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "traineecertificate") {
                    $response = $this->generateTraineesCertificateForOne($this->params['user_id'], $this->params['cohort_id']);
                } else {
                    $response = sizeof($this->params) > 0 ? $this->getTrainees($this->params['action']) : Errors::notFoundError("User trainees route not found, please try again?");
                }
                break;
            default:
                $response = Errors::notFoundError("User trainees route not found, please try again?");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getTrainees($cohortId)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = $current_user_role[0];
            $result = $this->traineersModel->getTrainees($cohortId, $user_role_details);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function TraineePerformanceLevelHandler($avarage)
    {
        switch (true) {
            case ($avarage >= 90 && $avarage <= 100):
                return "High Distinction";
            case ($avarage >= 80 && $avarage <= 89.9):
                return "Distinction";
            case ($avarage >= 70 && $avarage <= 79.9):
                return "Pass";
            case ($avarage >= 0 && $avarage <= 69.9):
                return "Failed";
            default:
                return "Invalid score";
        }

    }

    function filterHighScorers($trainee)
    {
        $level = $this->TraineePerformanceLevelHandler($trainee['average']);
        return $level != "Failed" && $level != "Invalid score" ? true : false;
    }

    private function generateTraineesCertificate($cohortId)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if cohort exists
            $cohortsExists = $this->cohortsModel->getOneCohort($cohortId);
            if (sizeof($cohortsExists) == 0) {
                return Errors::badRequestError("Cohort not found!, please try again?");
            }

            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($current_user_role) == 0) {
                return Errors::badRequestError("No current role found!, please try again?");
            }
            $user_role_details = $current_user_role[0];
            $role = $user_role_details['role_id'];
            switch ($role) {
                case '2':
                    // this is school level
                    if (!isset($user_role_details['school_code'])) {
                        return Errors::badRequestError("School not found!, please try again?");
                    }
                    $result = $this->traineersModel->getGenratedReportTraineesBySchool($cohortId, $user_role_details['school_code']);
                    // calculate trainee's avarage
                    $results = $this->calculateCombinedAverage($result);
                    return sizeof($result) > 0 ? $this->createPDFSample2($results) : Errors::badRequestError("Report not found!, please try again?");
                case '1':
                    $result = $this->traineersModel->getGenratedReportTraineesByUser($user_role_details['user_id'], $cohortId);
                    // calculate trainee's avarage
                    $results = $this->calculateCombinedAverage($result);
                    return sizeof($result) > 0 ? $this->createPDFSample2($results) : Errors::badRequestError("Report not found!, please try again?");
                default:
                    $result = $this->traineersModel->getGenratedReportTrainees($cohortId);
                    // calculate trainee's avarage
                    $results = $this->calculateCombinedAverage($result);
                    $filterTrainees = array_filter($results, array($this, 'filterHighScorers'));
                    if (sizeof($filterTrainees) > 0) {
                        return $this->createPDFSample2($filterTrainees);
                    } else {
                        return Errors::badRequestError("No trainees with high scores found!, please try again?");
                    }
            }
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Calculates the combined average for each user based on their unit marks.
     *
     * @param array $data An array of objects, where each object has the following properties:
     *   - `generalReportId`: (string) The unique identifier of the report.
     *   - `traineeId`: (string) The unique identifier of the trainee in training.
     *   - `userId`: (string) The unique identifier of the user.
     *   - `traineeName`: (string) The name of the trainee.
     *   - `traineePhone`: (string) The phone number of thrainee.
     *   - `staff_code`: (string) The unique identifier of the teacher or staff.
     *   - `cohortId`: (string) The unique identifier of cohorts.
     *   - `moduleId`: (string) The unique identifier of module.
     *   - `moduleName`: (string) The name of module.
     *   - `unitId`: (string) The unique identifier of unit in module.
     *   - `unitName`: (string) The name of unit.
     *   - `copMarks`: (int) The marks of cop report.
     *   - `progressMarks`: (int) The marks for teacher progress.
     *   - `gradeMarks`: (int) The marks for grade.
     *   - `feedback`: (int) The marks for teacher feedback.
     *   - `htNotesMarks`: (int) The marks for teacher notes.
     *   - `htClassMarks`: (int) The marks for teacher in class.
     *   - `age`: (int) The age of trainee.
     *   - `gender`: (string) The gender of trainee (FEMALE, MALE).
     *   - `disability`: (boolean) The disability is true if have one.
     *   - `district_code`: (string) The unique identifier of district.
     *   - `district_name`: (string) The name of district.
     *   - `sector_code`: (string) The unique identifier of sector.
     *   - `sector_name`: (string) The name of sector.
     *   - `school_code`: (string) The unique identifier of the school.
     *   - `school_name`: (string) The name of the school.
     *   - `trainingId`: (string) The unique identifier of training.
     *   - `trainingName`: (string) The name of training.
     *   - `cohortStart`: (Date) The starting date of cohorts.
     *   - `cohortEnd`: (Date) The ending date of cohorts.
     *
     * @return array An array of objects, where each object has the following properties:
     *   - `userId`: (string) The same `userId` as in the input data.
     *   - `staff_code`: (string) The same `staff_code` as in the input data.
     *   - `cohortId`: (string) The same `cohortId` as in the input data.
     *   - `traineeName`: (string) The same `traineeName` as in the input data.
     *   - `trainingName`: (string) The same `trainingName` as in the input data.
     *   - `cohortStart`: (Date) The same `cohortStart` as in the input data.
     *   - `cohortEnd`: (Date) The same `cohortEnd` as in the input data.
     *   - `unit_marks`: (Object) The Sum of each unit marks.
     *   - `average`: (float) The calculated combined average for the user.
     */

    public function calculateCombinedAverage(array $data): array
    {
        try {

            // Initialize an array to store combined averages
            $combinedAverages = [];

            // Loop through each user in the data
            foreach ($data as $row) {
                $userId = $row["userId"];

                // Check if user already has data in the combined averages array
                if (!isset($combinedAverages[$userId])) {
                    $combinedAverages[$userId] = [
                        "unit_marks" => [],
                        "userId" => $row["userId"],
                        "staff_code" => $row["staff_code"],
                        "cohortId" => $row["cohortId"],
                        "traineeName" => $row["traineeName"],
                        "cohortStart" => $row["cohortStart"],
                        "cohortEnd" => $row["cohortEnd"],
                        "trainingName" => $row["trainingName"],
                    ];
                }

                // Store marks for the current unit
                $combinedAverages[$userId]["unit_marks"][$row["unitId"]] = (int) $row["copMarks"] + (int) $row["progressMarks"] + (int) $row["gradeMarks"] + (int) $row["feedback"] + (int) $row["htNotesMarks"] + (int) $row["htClassMarks"];
            }

            // Calculate average for each unit for each user
            foreach ($combinedAverages as $userId => &$userAvg) {
                $numUnits = count($userAvg["unit_marks"]); // Get the number of units

                // Initialize sum of averages
                $averageSum = 0;

                // Loop through each unit and add its average to the sum
                foreach ($userAvg["unit_marks"] as $unit => $marks) {
                    $averageSum += $marks / 6; // Calculate average for current unit and add
                }

                // Calculate final average by dividing sum by number of units
                $userAvg["average"] = $averageSum / $numUnits;
            }

            return $combinedAverages;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function generateTraineesCertificateForOne($staff_code, $cohortId)
    {
        try {
            // checking if cohort exists
            $cohortsExists = $this->cohortsModel->getOneCohort($cohortId);
            if (sizeof($cohortsExists) == 0) {
                return Errors::badRequestError("Cohort not found!, please try again?");
            }

            $result = $this->traineersModel->getGenratedReportTraineesByStaff($staff_code, $cohortId);
            // calculate trainee's avarage
            $results = $this->calculateCombinedAverage($result);

            if (sizeof($result) > 0) {
                return $this->createPDFSample2($results);
            }

            return Errors::badRequestError("Report not found!, please try again?");
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function displayDateHandler($dateString)
    {
        // convert date into timestamp
        $timestamp = strtotime($dateString);

        // format date
        $formattedDate = date("F jS Y", $timestamp);

        return $formattedDate;
    }

    public function createPDFSample2($trainees)
    {
        // create new PDF document
        $pdf = new Fpdi('L', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('FHI');
        $pdf->SetTitle('Certificate of completion');
        $pdf->SetSubject('Trainees certificate');
        $pdf->SetKeywords('FHI, PDF, TCPDF');

        // remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set auto page break and bottom margin to zero
        $pdf->setAutoPageBreak(true, 0);

        // ---------------------------------------------------------
        $pdf->startPageGroup();
        foreach ($trainees as $key => &$value) {
            $staffCode = $value['staff_code'];
            $cohortId = $value['cohortId'];
            $avarage = isset($value['average']) ? $value['average'] : 0;
            $pdf->AddPage();

            // Set the template file
            $template = $this->homeDir . '/public/trainee_certificate_sample_A4.pdf';

            // Add a page using the template
            $pdf->setSourceFile($template);
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0);

            // adding header paragraph
            $pdf->SetFont('Times', '', 12);
            $textHeader = "Florida State University, through the Tunoze Gusoma project, implemented in Rwanda jointly with Rwanda Basic Education Board under Cooperative Agreement between USAID and FHI360, \nawards to";
            $pdf->MultiCell(190, 13, $textHeader, 0, 'C', false, 1, 10, 60);

            // adding Recipient Name
            $pdf->SetFont('Times', 'B', 20);
            $recipientName = $value['traineeName'];
            $pdf->MultiCell(190, 13, $recipientName, 0, 'C', false, 1, 10, 80);

            // Complition
            $pdf->SetFont('Times', 'I', 25);
            $complition = "a Certificate of Completion with \n" . $this->TraineePerformanceLevelHandler($avarage);
            $pdf->MultiCell(190, 13, $complition, 0, 'C', false, 1, 10, 95);

            // Message
            $pdf->SetFont('Times', 'I', 10);
            $message = "for successfully completing a Professional \nDevelopment Course for Rwandan Teacher \nEducation Practitioners titled";
            $pdf->MultiCell(190, 10, $message, 0, 'C', false, 1, 10, 120);

            // title
            $pdf->SetFont('Times', 'B', 10);

            $title = '"' . $value['trainingName'] . '"';
            $pdf->MultiCell(190, 13, $title, 0, 'C', false, 1, 10, 135);

            // date
            $pdf->SetFont('Times', 'I', 10);
            $date = "between " . $this->displayDateHandler($value['cohortStart']) . " and " . $this->displayDateHandler($value['cohortEnd']) . ".";
            $pdf->MultiCell(190, 13, $date, 0, 'C', false, 1, 10, 140);

            // Director names
            $pdf->SetFont('Times', 'B', 12);
            $pdf->SetXY(10, 150);
            $pdf->Ln();

            // Define data for the table
            $data = array(
                array('Dr. Nelson Mbarushimana', 'Dr. Aliou Tall', 'Mr. Rabieh Razzouk'),
                array('Director General for Rwanda', 'Director, Education Office', 'Director, Learning Systems Institute'),
            );

            // Set width for each column
            $columnWidths = array(70, 70, 70);

            // Loop through the data and add rows and columns
            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    // Add cell with content
                    $pdf->Cell($columnWidths[$key], 5, $value, 0, 0, 'C');
                }
                $pdf->SetFont('Times', 'I', 10);
                // Move to the next line
                $pdf->Ln();
            }

            // BarCode
            // set style for barcode
            $style = array(
                'border' => 2,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => false, //array(255,255,255)
                'module_width' => 1, // width of a single module in points
                'module_height' => 1, // height of a single module in points
            );
            // QRCODE,Q : QR-CODE Better error correction
            $host = $_SERVER['HTTP_HOST'];
            $pos = strpos($host, "localhost");
            $localhostUrl = "http://" . $host . "/trainee/certificate/verify/" . $staffCode . "/" . $cohortId;
            $productionUrl = "https://elearning.reb.rw/rebmis/trainee/certificate/verify/" . $staffCode . "/" . $cohortId;
            $url = $pos === false ? $productionUrl : $localhostUrl;
            $pdf->write2DBarcode($url, 'QRCODE,Q', 240, 150, 30, 30, $style, 'R');

            // Warning
            $pdf->SetXY(10, 195);
            $pdf->SetFont('Times', 'I', 10);
            $warning = "Note: This certificate is valid upon presentation of a detailed transcript indicating courses completed and passed.";
            $pdf->Write(1, $warning, '', false, 'R', true);
            // $pdf->MultiCell(0, 0, $warning, 0, 'C', false, 1, 10, 186);
        }
        // ---------------------------------------------------------

        // Close and output PDF document
        $pdf->Output('FHI_Training_Certificate.pdf', 'D');
    }
}

$controller = new TraineersController($this->db, $request_method, $params);
$controller->processRequest();
