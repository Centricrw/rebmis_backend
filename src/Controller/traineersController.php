<?php
namespace Src\Controller;

use setasign\Fpdi\Tcpdf\Fpdi;
use Src\Models\CohortsModel;
use Src\Models\DirectorSignatureModel;
use Src\Models\ReportModel;
use Src\Models\TraineersModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\Validations\BasicValidation;

class TraineersController
{
    private $db;
    private $traineersModel;
    private $request_method;
    private $userRoleModel;
    private $directorSignatureModel;
    private $reportModel;
    private $cohortsModel;
    private $params;
    private $homeDir;
    private $widthColumn;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->traineersModel = new TraineersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->cohortsModel = new CohortsModel($db);
        $this->reportModel = new ReportModel($db);
        $this->directorSignatureModel = new DirectorSignatureModel($db);
        $this->homeDir = dirname(__DIR__, 2);
        $this->widthColumn = 70;
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "certificate") {
                    $response = $this->generateTraineesCertificate($this->params['user_id']);
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "traineecertificate") {
                    $response = $this->generateTraineesCertificateForOne($this->params['user_id'], $this->params['cohort_id']);
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "selected") {
                    $response = $this->generateCertificateForSelectedTrainee($this->params['user_id'], $this->params['cohort_id']);
                } else if (sizeof($this->params) > 0 && $this->params['action'] == "status") {
                    $response = $this->getTraineeByStatus($this->params['user_id']);
                } else {
                    $response = sizeof($this->params) > 0 ? $this->getTrainees($this->params['action']) : Errors::notFoundError("User trainees route not found, please try again?");
                }
                break;
            case 'PATCH':
                if (sizeof($this->params) > 0 && $this->params['action'] == "status") {
                    $response = $this->updateTraineeStatusHandler($this->params['user_id']);
                } else {
                    $response = Errors::notFoundError("User trainees route not found, please try again?");
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
            case ($avarage >= 70 && $avarage <= 100):
                return "Distinction";
            case ($avarage >= 60 && $avarage <= 69.9):
                return "Satisfactory";
            case ($avarage >= 0 && $avarage <= 59.9):
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

    function removeExtraSpacesAndNewlines($string)
    {
        // Replace consecutive whitespace characters with a single space:
        $string = preg_replace('/\s+/', ' ', $string);

        // Optionally, replace consecutive newlines with a single newline:
        if (!stristr($string, "\r")) { // No carriage returns, so use \n
            $string = preg_replace('/\n+/', "\n", $string);
        } else { // Remove all newlines if carriage returns exist
            $string = str_replace(["\r\n", "\r", "\n"], "", $string);
        }

        return $string;
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
            // get director signature
            $signatures = $this->directorSignatureModel->selectDirectorSignatureBYTraining($cohortsExists[0]['trainingId']);

            // find logged in user current role
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
                    $filterTraineesLevel2 = array_filter($results, array($this, 'filterHighScorers'));
                    return sizeof($filterTraineesLevel2) > 0 ? $this->createPDFSample2($filterTraineesLevel2, $signatures) : Errors::badRequestError("Report not found!, please try again?");
                case '1':
                    $result = $this->traineersModel->getGenratedReportTraineesByUser($user_role_details['user_id'], $cohortId);
                    // calculate trainee's avarage
                    $results = $this->calculateCombinedAverage($result);
                    $filterTraineesLevel1 = array_filter($results, array($this, 'filterHighScorers'));
                    return sizeof($filterTraineesLevel1) > 0 ? $this->createPDFSample2($filterTraineesLevel1, $signatures) : Errors::badRequestError("Report not found!, please try again?");
                default:
                    $result = $this->traineersModel->getGenratedReportTrainees($cohortId);
                    // calculate trainee's avarage
                    $results = $this->calculateCombinedAverage($result);
                    $filterTrainees = array_filter($results, array($this, 'filterHighScorers'));
                    if (sizeof($filterTrainees) > 0) {
                        return $this->createPDFSample2($filterTrainees, $signatures);
                    } else {
                        return Errors::badRequestError("No trainees with high scores found!, please try again?");
                    }
            }
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Calculates the combined average for each user based on their chapter marks.
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
     *   - `chapterId`: (string) The unique identifier of chapter in module.
     *   - `chapterName`: (string) The name of chapter.
     *   - `copMarks`: (int) The marks of cop report.
     *   - `courseNavigation`: (int) The marks for teacher progress.
     *   - `endOfChapter`: (int) The marks for grade.
     *   - `selfAssesment`: (int) The marks for teacher selfAssesment.
     *   - `reflectionNotes`: (int) The marks for teacher notes.
     *   - `classroomApplication`: (int) The marks for teacher in class.
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
     *   - `chapter_marks`: (Object) The Sum of each chapter marks.
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
                        "chapter_marks" => [],
                        "userId" => $row["userId"],
                        "staff_code" => $row["staff_code"],
                        "cohortId" => $row["cohortId"],
                        "traineeName" => $row["traineeName"],
                        "cohortStart" => $row["cohortStart"],
                        "cohortEnd" => $row["cohortEnd"],
                        "trainingName" => $row["trainingName"],
                    ];
                }

                // Store marks for the current chapter
                $combinedAverages[$userId]["chapter_marks"][$row["chapterId"]] = [
                    "copMarks" => (int) $row["copMarks"],
                    "courseNavigation" => (int) $row["courseNavigation"],
                    "endOfChapter" => (int) $row["endOfChapter"],
                    "selfAssesment" => (int) $row["selfAssesment"],
                    "reflectionNotes" => (int) $row["reflectionNotes"],
                    "classroomApplication" => (int) $row["classroomApplication"],
                    "endOfModule" => (int) $row["endOfModule"],
                    "endOfCourse" => (int) $row["endOfCourse"],
                    "selfStudy" => (int) $row["selfStudy"],
                    "coaching" => (int) $row["coaching"],
                ];
            }

            // Calculate average for each chapter for each user
            foreach ($combinedAverages as $userId => &$userAvg) {
                $numChapters = count($userAvg["chapter_marks"]); // Get the number of chapters

                // Initialize sum of averages
                $copMarksAverageSum = 0;
                $courseNavigationAverageSum = 0;
                $endOfChapterAverageSum = 0;
                $selfAssesmentAverageSum = 0;
                $reflectionNotesAverageSum = 0;
                $classroomApplicationAverageSum = 0;
                $endOfModuleAverageSum = 0;
                $endOfCourseAverageSum = 0;
                $selfStudyAverageSum = 0;
                $coachingAverageSum = 0;
                // Loop through each chapter and add its average to the sum
                foreach ($userAvg["chapter_marks"] as $chapter => $marks) {
                    $copMarksAverageSum += (int) $marks['copMarks'];
                    $courseNavigationAverageSum += (int) $marks['courseNavigation'];
                    $endOfChapterAverageSum += (int) $marks['endOfChapter'];
                    $selfAssesmentAverageSum += (int) $marks['selfAssesment'];
                    $reflectionNotesAverageSum += (int) $marks['reflectionNotes'];
                    $classroomApplicationAverageSum += (int) $marks['classroomApplication'];
                    $endOfModuleAverageSum += (int) $marks['endOfModule'];
                    $endOfCourseAverageSum += (int) $marks['endOfCourse'];
                    $selfStudyAverageSum += (int) $marks['selfStudy'];
                    $coachingAverageSum += (int) $marks['coaching'];
                }

                echo "endOfModuleSum: " . $endOfModuleAverageSum;

                $courseNavigationAverageSum = (($courseNavigationAverageSum / $numChapters) * 20) / 100;
                $endOfChapterAverageSum = (($endOfChapterAverageSum / $numChapters) * 10) / 100;
                $selfAssesmentAverageSum = (($selfAssesmentAverageSum / $numChapters) * 10) / 100;
                $endOfModuleAverageSum = (($endOfModuleAverageSum / $numChapters) * 30) / 100;
                $endOfCourseAverageSum = (($endOfCourseAverageSum / $numChapters) * 20) / 100;

                $copMarksAverageSum = ($copMarksAverageSum / $numChapters);
                $reflectionNotesAverageSum = ($reflectionNotesAverageSum / $numChapters);
                $classroomApplicationAverageSum = ($classroomApplicationAverageSum / $numChapters);
                $selfStudyAverageSum = ($selfStudyAverageSum / $numChapters);
                $coachingAverageSum = ($coachingAverageSum / $numChapters);

                $teacherPracticeAvarageSum = ((($copMarksAverageSum + $reflectionNotesAverageSum + $classroomApplicationAverageSum + $selfStudyAverageSum + $coachingAverageSum) / 5) * 10) / 100;

                // Calculate final average by dividing sum by number of chapters
                $userAvg["average"] = $courseNavigationAverageSum + $endOfChapterAverageSum + $selfAssesmentAverageSum + $endOfModuleAverageSum + $endOfCourseAverageSum + $teacherPracticeAvarageSum;

                echo "courseNavigationAverageSum: " . $courseNavigationAverageSum;
                echo "endOfChapterAverageSum: " . $endOfChapterAverageSum;
                echo "selfAssesmentAverageSum: " . $selfAssesmentAverageSum;
                echo "endOfModuleAverageSum: " . $endOfModuleAverageSum;
                echo "endOfCourseAverageSum: " . $endOfCourseAverageSum;
                echo "teacherPracticeAvarageSum: " . $teacherPracticeAvarageSum;
                echo "average: " . $userAvg["average"];
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

            // get director signature
            $signatures = $this->directorSignatureModel->selectDirectorSignatureBYTraining($cohortsExists[0]['trainingId']);

            $filterTrainees = array_filter($results, array($this, 'filterHighScorers'));
            if (sizeof($filterTrainees) > 0) {
                return $this->createPDFSample2($filterTrainees, $signatures);
            }

            return Errors::badRequestError("Report not found!, please try again?");
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function generateCertificateForSelectedTrainee($cohortId, $deisplay = false)
    {
        try {
            $names = [
                "Nyirahabihirwe Pacifique",
                "Dusabeyezu Jean De Dieu",
                "Beatha Uwamahoro",
                "Nyirakabuga Scholastique",
                "Niyitegeka Marceline",
                "Mukagatsinzi Mediatrice",
                "Kanyamibwa Alphonse",
                "Mukeshimana Jacqueline",
                "Ingabire Cecile",
                "Niyibizi Alphonsine",
                "Uwamahoro Delphine",
                "Bazubagira Marie Chantal",
                "Mukamana Eugenie",
                "Mukubwire Emerance",
                "Niyomukiza Valens",
                "Nizeyimana Deogratias",
                "Mutuyeyezu Jacqueline",
                "Mukamusafiri Venerande",
                "Uwamahoro Assoumpta",
                "Nibakure Chantal",
                "Uwamahoro Jeanette",
                "Mujawamariya Annoncee",
                "Musoni Thadee",
                "Muhaweniyera Edelbourgue",
                "Ayobangira Immaculee",
                "Uwajeneza Francine",
                "Uwizeyimana Marie Chantal",
                "Mukamusoni Clarisse",
                "Nyirabakiga Laurence",
                "Uwineza Olive",
                "Beza Nakure Vestine",
                "Mukaneza Donathile",
                "Mukamusoni Elisabeth",
                "Niyongabire Marie",
                "Twizerimana Dada",
                "Olive Uwimbabazi",
                "Leonilla Mukasebanani",
                "Ishimwe Liliane",
                "Mushimiyimana Georgine",
                "Nyiransabimana Julienne",
                "Nikuze Therese",
                "Mukamana Providence",
                "Akimana Alice",
                "Mukansanga Consolee",
                "Ahishakiye Berte",
                "Nyiraneza Jaqueline",
                "Mukamana Marie Goretti",
                "Nabitanga Nankema",
                "KANYANGE ALINE",
                "Jacqueline Uwimana",
                "Annonciata Mujawingoma",
                "Ufitinema Marie Gratie",
                "Mukamana Marie Gorette",
                "Ntawe Marie Goreth",
                "Mujawimana Emma Marie",
            ];
            $trainees = array();
            $notFound = array();
            // checking if cohort exists
            $cohortsExists = $this->cohortsModel->getOneCohort($cohortId);
            if (sizeof($cohortsExists) == 0) {
                return Errors::badRequestError("Cohort not found!, please try again?");
            }

            foreach ($names as $key => $value) {
                $details = $this->traineersModel->getGenratedReportTraineesByName($value, $cohortId);
                if (count($details) > 0) {
                    foreach ($details as $index => $element) {
                        array_push($trainees, $element);
                    }
                } else {
                    array_push($notFound, $value);
                }
            }
            // calculate trainee's avarage
            $results = $this->calculateCombinedAverage($trainees);

            if ($deisplay == "true") {
                // get director signature
                $signatures = $this->directorSignatureModel->selectDirectorSignatureBYTraining($cohortsExists[0]['trainingId']);
                $filterTrainees = array_filter($results, array($this, 'filterHighScorers'));
                if (sizeof($details) > 0 && count($filterTrainees) > 0) {
                    return $this->createPDFSample2($filterTrainees, $signatures);
                }
                return Errors::badRequestError("Report not found!, please try again?");
            } else {
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode([
                    "not_found" => $notFound,
                ]);
                return $response;
            }
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function displayDateHandler($dateString)
    {
        // convert date into timestamp
        $timestamp = strtotime($dateString);

        // format date
        $formattedDate = date("F Y", $timestamp);

        return $formattedDate;
    }

    function getTraineeByStatus($status = "Removed")
    {
        $created_by_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->traineersModel->selectTraineeBYStatus($status);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function updateTraineeStatusHandler($trainee_id)
    {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $created_by_user_id = AuthValidation::authorized()->id;
        try {
            // validating input
            $validateThisValues = [
                "current_status" => "Current status is required",
                "new_status" => "New Status is required",
            ];
            $validateUserInputData = BasicValidation::validate($data, $validateThisValues);
            if (!$validateUserInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateUserInputData['message']);
            }

            // checking if not === 'Shortlisted','Approved','Invited','Rejected','Removed'
            $validStatus = ['Shortlisted', 'Approved', 'Invited', 'Rejected', 'Removed'];
            if (!in_array($data["new_status"], $validStatus)) {
                return Errors::unprocessableEntityResponse("New status has invalid status, must be one this Shortlisted, Approved, Invited, Rejected and Removed");
            }
            if (!in_array($data["current_status"], $validStatus)) {
                return Errors::unprocessableEntityResponse("New status has invalid status, must be one this Shortlisted, Approved, Invited, Rejected and Removed");
            }

            // checking if user is availeble
            $traineeExists = $this->traineersModel->selectTraineeBYId($trainee_id);
            if (count($traineeExists) == 0) {
                return Errors::badRequestError("Trainee not found, plaese try again later?");
            }

            // update tarainee status
            $updateStatus = $this->traineersModel->updateTraineeStatus($data, $trainee_id);
            $romovedExists = $data["new_status"] == "Removed" || $data["current_status"] == "Removed" ? true : false;
            if (isset($updateStatus) && $romovedExists) {
                // update general report status if trainee Removed
                $traineeExistsInReaport = $this->reportModel->selectGeneralReportByTraineeId($trainee_id);
                if (count($traineeExistsInReaport) > 0) {
                    $status = $data["new_status"] == "Removed" ? "Removed" : "Active";
                    $updateReport = $this->reportModel->updateTraineeGeneralReportStatus($status, $trainee_id);
                }
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "message" => "Trainee " . $data["new_status"] . " successfuly",
                "Updated_genaral_report" => isset($updateReport) ? true : false,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    public function createPDFSample2($trainees, $signatures)
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
            $textHeader = "FHI 360, through the USAID Tunoze Gusoma project, implemented in Rwanda jointly\n with Rwanda Basic Education Board, awards to:";
            $pdf->MultiCell(0, 13, $textHeader, 0, 'C', false, 1, 10, 60);

            // adding Recipient Name
            $pdf->SetFont('Times', 'B', 20);
            $recipientName = $this->removeExtraSpacesAndNewlines($value['traineeName']);
            $pdf->MultiCell(0, 13, $recipientName, 0, 'C', false, 1, 10, 80);

            // Complition
            $complitionStatus = $this->TraineePerformanceLevelHandler($avarage);
            $complitionMessage = $complitionStatus == "Satisfactory" ? "a Certificate of $complitionStatus \nCompletion" : "a Certificate of Completion with \n" . $complitionStatus;
            $pdf->SetFont('Times', 'I', 25);
            $pdf->MultiCell(0, 13, $complitionMessage, 0, 'C', false, 1, 10, 95);

            // Message
            $pdf->SetFont('Times', 'I', 10);
            $message = "for successfully completing a Blended Learning Continuous Professional Development \nCourse for Rwandan In- Service Primary Teachers titled:";
            $pdf->MultiCell(0, 10, $message, 0, 'C', false, 1, 10, 120);

            // title
            $pdf->SetFont('Times', 'B', 10);

            $title = '"' . $this->removeExtraSpacesAndNewlines($value['trainingName']) . '"';
            $pdf->MultiCell(0, 13, $title, 0, 'C', false, 1, 10, 135);

            // date
            $pdf->SetFont('Times', 'I', 10);
            $date = "between " . $this->displayDateHandler($value['cohortStart']) . " and " . $this->displayDateHandler($value['cohortEnd']) . ".";
            $pdf->MultiCell(0, 13, $date, 0, 'C', false, 1, 10, 140);

            // Director names
            $pdf->SetFont('Times', 'B', 12);
            $pdf->SetXY(20, 172);
            // Define data for the table
            // Get the number of directors
            $directorCount = count($signatures);

            $data = array();
            // Loop through each director's information key
            $infoKeys = array('director_signature_url', 'director_name', 'director_role', 'director_institution');
            // Add director names to the first row
            $data[0] = array();
            for ($i = 0; $i < $directorCount; $i++) {
                $data[0][] = $signatures[$i]['director_signature_url'];
            }
            // Add remaining information for each director in separate rows
            for ($j = 1; $j < count($infoKeys); $j++) {
                $data[$j] = array();
                for ($i = 0; $i < $directorCount; $i++) {
                    $data[$j][] = $signatures[$i][$infoKeys[$j]];
                }
            }

            // Set width for each column
            $this->widthColumn = 210 / $directorCount;
            $widthColumnHandler = function ($values) {
                return $this->widthColumn;
            };
            $columnWidths = array_map($widthColumnHandler, $signatures);

            // Loop through the data and add rows and columns
            $absolute_y = 180;
            $countRows = 0;
            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    // Add cell with content
                    if (strpos($value, "public/uploads/") !== false) {
                        $absolute_X = ($this->widthColumn * $key) + 20;
                        $pdf->Image($this->homeDir . "/" . $value, $absolute_X, 155, 50, 15);
                    } else {
                        $pdf->Cell($columnWidths[$key], 5, $value, 0, 0, 'L');
                    }
                }
                $countRows++;
                if ($countRows > 1) {
                    $pdf->SetFont('Times', '', 10);
                    $pdf->SetXY(20, $absolute_y);
                    $absolute_y += 5;
                }
                // Move to the next line
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
            $pdf->write2DBarcode($url, 'QRCODE,Q', 240, 160, 30, 30, $style, 'R');

            // Warning
            $pdf->SetXY(10, 192);
            $pdf->SetFont('Times', '', 10);
            $warning = "Scan to download the transcript.          ";
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
