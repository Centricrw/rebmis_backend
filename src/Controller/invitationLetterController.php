<?php
namespace Src\Controller;

use Src\Models\CohortconditionModel;
use Src\Models\InvitationLetterModel;
use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;
use TCPDF;

class InvitationLetterController
{
    private $db;
    private $invitationLetterModel;
    private $usersModel;
    private $trainingsModel;
    private $userRoleModel;
    private $cohortconditionModel;
    private $request_method;
    private $params;
    private $homeDir;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->invitationLetterModel = new InvitationLetterModel($db);
        $this->trainingsModel = new TrainingsModel($db);
        $this->usersModel = new UsersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->cohortconditionModel = new CohortconditionModel($db);
        $this->homeDir = dirname(__DIR__, 2);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "training") {
                    $response = $this->getAllInvintationLetterAssignedToTraining($this->params['id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "one") {
                    $response = $this->getOneInvitationLetterByID($this->params['id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "letter") {
                    $response = $this->generateTeacherTrainingLetter($this->params['id']);
                } else {
                    $response = $this->getAllInvintationLetters();
                }
                break;
            case "POST":
                $response = $this->createNewInvitationTammplete();
                break;
            case "PUT":
                $response = $this->updateInvitationLetter($this->params['id']);
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

    /**
     * Create new invitation tamplete letter
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function createNewInvitationTammplete()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // validating input data
        $validateData = self::validateInvitationLetterInput($data);
        if (!$validateData['validated']) {
            return Errors::unprocessableEntityResponse($validateData['message']);
        }
        // checking if training id exists
        $trainingExists = $this->trainingsModel->getOneTraining($data['cohort_id']);
        if (sizeof($trainingExists) == 0) {
            return Errors::notFoundError("Training id not found, please try again?");
        }
        // Generate training center id
        $generated_invitation_tammplete_id = UuidGenerator::gUuid();
        $data['id'] = $generated_invitation_tammplete_id;
        try {
            // checking if letter exists for that training
            $invitationTammpleteExists = $this->invitationLetterModel->selectInvintationLetterByType($data['cohort_id'], $data['letter_type']);
            if (sizeof($invitationTammpleteExists) > 0) {
                return Errors::badRequestError("This invitation letter already exists, please try again?");
            }
            $this->invitationLetterModel->insertNewInvitationTammplete($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "id" => $data['id'],
                "title" => $data['title'],
                "body" => $data['body'],
                "cohort_id" => $data['cohort_id'],
                "letter_type" => $data['letter_type'],
                "message" => "Invitation letter tamplete created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all invitation tamplete
     * @param {null}
     * @return {OBJECT} {results}
     */
    public function getAllInvintationLetters()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->invitationLetterModel->selectAllInvintationLetter();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * all invitation tamplete letter assigned to training
     * @param {STRING} cohort_id
     * @return {OBJECT} {results}
     */
    public function getAllInvintationLetterAssignedToTraining($cohort_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try { 
            $results = $this->invitationLetterModel->selectInvintationLetterByCohort($cohort_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get One invitation tamplete letter
     * @param {STRING} id
     * @return {OBJECT} {results}
     */
    public function getOneInvitationLetterByID($id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->invitationLetterModel->selectInvintationLetterById($id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * update invitation tamplete letter
     * @param {STRING} id
     * @return {OBJECT} {results}
     */
    public function updateInvitationLetter($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // validating input data
        $validateData = self::validateInvitationLetterInput($data);
        if (!$validateData['validated']) {
            return Errors::unprocessableEntityResponse($validateData['message']);
        }
        // checking if invitation Letter exists
        $invitationLetterExists = $this->invitationLetterModel->selectInvintationLetterById($id);
        if (sizeof($invitationLetterExists) == 0) {
            return Errors::notFoundError("Tamplete invitation latter id not found, please try again?");
        }

        if ($invitationLetterExists[0]['cohort_id'] !== $data['cohort_id']) {
            // checking if training id exists
            $trainingExists = $this->trainingsModel->getOneTraining($data['cohort_id']);
            if (sizeof($trainingExists) == 0) {
                return Errors::notFoundError("Training id not found, please try again?");
            }

            // checking if letter exists for that training
            $invitationTammpleteExists = $this->invitationLetterModel->selectInvintationLetterByType($data['cohort_id'], $data['letter_type']);
            if (sizeof($invitationTammpleteExists) > 0) {
                return Errors::badRequestError("This invitation letter already exists, please try again?");
            }

        }

        // getting id
        $data['id'] = $id;
        try {
            $this->invitationLetterModel->updateInvintationLetter($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "id" => $data['id'],
                "title" => $data['title'],
                "body" => $data['body'],
                "cohort_id" => $data['cohort_id'],
                "letter_type" => $data['letter_type'],
                "status" => $data['status'],
                "message" => "Invitation letter tamplete  updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // checking if invitetion letter exists for this training
    function invitationExistsHandler($cohort_id, $letter_type)
    {
        $invitationExists = $this->invitationLetterModel->selectInvintationLetterByCohortIdAndType($cohort_id, $letter_type);
        if (sizeof($invitationExists) == 0) {
            $response = Errors::notFoundError("This training does not have training letter for $letter_type, please contact admistrator?");
            return $response;
        }
        return $invitationExists;
    }

    // get dde letter and traineers details
    function getDDELetterAndDistrictTraineers($cohort_id, $userDetails, $district_code)
    {
        $ddeInvitation = $this->invitationExistsHandler($cohort_id, "DDE");
        $classTeacherInvitation = $this->invitationExistsHandler($cohort_id, "Class Teacher");
        // checking if users is assigned to that training
        $traineerExists = $this->cohortconditionModel->selectTraineeOnThatDistrict($cohort_id, $district_code);
        if (sizeof($traineerExists) == 0) {
            $response = Errors::notFoundError("Traineer not found on this training, please contact admistrator?");
            return $response;
        }
        return $this->genarateTampleteInvitaioninPDF($userDetails, $ddeInvitation[0], $classTeacherInvitation[0], $traineerExists);

    }
    // get Head teacher letter and traineers details
    function getHeadTeacherLetterAndSchoolTraineers($cohort_id, $userDetails, $school_code)
    {
        $headTeacherInvitation = $this->invitationExistsHandler($cohort_id, "Head Teacher");
        $classTeacherInvitation = $this->invitationExistsHandler($cohort_id, "Class Teacher");
        // checking if users is assigned to that training
        $traineerExists = $this->cohortconditionModel->selectTraineesOnThatSchools($cohort_id, $school_code);
        if (sizeof($traineerExists) == 0) {
            $response = Errors::notFoundError("Traineer not found on this training, please contact admistrator?");
            return $response;
        }
        return $this->genarateTampleteInvitaioninPDF($userDetails, $headTeacherInvitation[0], $classTeacherInvitation[0], $traineerExists);
    }
    // get classTeacher details
    function getTraineersDetails($cohort_id, $userDetails)
    {
        // checking if users is assigned to that training 
        $traineerExists = $this->cohortconditionModel->selectTraineeByUserIDAndTrainingID($userDetails['user_id'], $cohort_id);
        if (sizeof($traineerExists) == 0) {
            $response = Errors::notFoundError("Traineer not found on this training, please contact admistrator?");
            return $response;
        }
        $invitation = $this->invitationExistsHandler($cohort_id, "Class Teacher");
        return $this->genarateTampleteInvitaioninPDF($userDetails, $invitation[0]);
    }

    // generate training letter handler
    public function generateTeacherTrainingLetter($cohort_id)
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // getting users details
        $userResult = $this->usersModel->findOneUser($logged_user_id);
        if (sizeof($userResult) == 0) {
            $response = Errors::notFoundError("User Not found, please contact admistrator?");
            return $response;
        }
        // getting user role
        $user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
        if (sizeof($user_role) == 0) {
            $response = Errors::notFoundError("This user does not have role, please contact admistrator?");
            return $response;
        }
        // is DDE
        if ($user_role[0]['role_id'] == 3) {
            return $this->getDDELetterAndDistrictTraineers($cohort_id, $userResult[0], $user_role[0]['district_code']);
        }
        // is Head Teacher
        if ($user_role[0]['role_id'] == 2) {
            return $this->getHeadTeacherLetterAndSchoolTraineers($cohort_id, $userResult[0], $user_role[0]['school_code']);
        }
        // is Class Teacher
        if ($user_role[0]['role_id'] == 1) {
            return $this->getTraineersDetails($cohort_id, $userResult[0]);
        }
        
        $response = Errors::badRequestError("This user does not have training letter role, please contact admistrator?");
        return $response;
    }

    /**
     * Genarate training tamplete invitation letter
     * @param {OBJECT, OBJECT} $cohort_id, $letter_type
     * @return {OBJECT} {results}
     */
    public function genarateTampleteInvitaioninPDF($teacherDetails, $invitationDetails, $classTeacherInvitation = "", $traineers = [])
    {

        //end
        $date = date("d F Y");
        // initiate FPDI
        $pdf = new TCPDF();
        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // Start first Page Group
        $pdf->startPageGroup();
        // add a page
        $pdf->AddPage('P', 'A4', false, true);

        // adding logo
        $pdf->Image($this->homeDir . '/public/logos/REB_Logo.png', 90, 5, 30);
        $pdf->SetXY(5, 30);
        $pdf->writeHTML("<hr style='background-color: #005198; height: 5px;'>", true, false, false, false, '');

        // adding Date
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY(10, 30);
        $pdf->Ln();
        $pdf->Cell(190, 5, "Kigali on: $date", 0, 1, 'R');
        $pdf->Cell(190, 5, "No ..03 /REB/06/2023", 0, 0, 'R');
        $pdf->Ln();

        // adding address
        $pdf->SetFont('Times', 'B', 12);
        $pdf->SetXY(10, 40);
        $pdf->Write(1, $teacherDetails['full_name'], '', false, '', true);
        $pdf->SetFont('Times', '', 12);
        $pdf->Write(1, $invitationDetails['letter_type'], '', false, '', true);
        $pdf->Write(1, "Email: " . $teacherDetails['email'], '', false, '', true);
        $pdf->Write(1, "Phone: " . $teacherDetails['phone_numbers'], '', false, '', true);

        // adding letter title
        // use Re: for request and Subject for inforation
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY(10, 65);
        $pdf->Write(4, "Dear " . $teacherDetails['full_name'], '', false, '', true);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Write(1, "Re: " . $invitationDetails['title'], '', false, '', true);

        // body
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY(10, 85);
        $pdf->writeHTML($invitationDetails['body']);

        // Body date done
        $pdf->Ln(10);
        $pdf->Write(5, "We highly appreciate your usual cooperation.");

        // footer
        $pdf->Ln(15);
        $pdf->Write(5, 'Yours Sincerely.');

        // Moyor
        $pdf->Ln(15);
        $pdf->Write(5, 'Director General: ');
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Write(5, "Dr. MBARUSHIMANA Nelson");

        // footer body
        $pdf->Ln(15);
        $pdf->Write(5, "Cc:
        Head of ICT in Department /REB
        Corporate Services Division Manager /REB");

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
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $pdf->write2DBarcode($url, 'QRCODE,Q', 130, 190, 50, 50, $style, 'R');
        // check if threre trainees for this training
        if ($classTeacherInvitation !== "" && sizeof($traineers) > 0) {
            // Start second Page Group
            $pdf->startPageGroup();
            foreach ($traineers as $key => $value) {
                // add a page
                $pdf->AddPage('P', 'A4', false, true);

                // adding logo
                $pdf->Image($this->homeDir . '/public/logos/REB_Logo.png', 90, 5, 30);
                $pdf->SetXY(5, 30);
                $pdf->writeHTML("<hr style='background-color: #005198; height: 5px;'>", true, false, false, false, '');

                // adding Date
                $pdf->SetFont('Times', '', 12);
                $pdf->SetXY(10, 30);
                $pdf->Ln();
                $pdf->Cell(190, 5, "Kigali on: $date", 0, 1, 'R');
                $pdf->Cell(190, 5, "No ..03 /REB/06/2023", 0, 0, 'R');
                $pdf->Ln();

                // adding address
                $pdf->SetFont('Times', 'B', 12);
                $pdf->SetXY(10, 40);
                $pdf->Write(1, $value['traineeName'], '', false, '', true);
                $pdf->SetFont('Times', '', 12);
                $pdf->Write(1, $classTeacherInvitation['letter_type'], '', false, '', true);
                $pdf->Write(1, "Email: ..................", '', false, '', true);
                $pdf->Write(1, "Phone: " . $value['traineePhone'], '', false, '', true);

                // adding letter title
                // use Re: for request and Subject for inforation
                $pdf->SetFont('Times', '', 12);
                $pdf->SetXY(10, 65);
                $pdf->Write(4, "Dear " . $value['traineeName'], '', false, '', true);
                $pdf->SetFont('Times', 'B', 12);
                $pdf->Write(1, "Re: " . $classTeacherInvitation['title'], '', false, '', true);

                // body
                $pdf->SetFont('Times', '', 12);
                $pdf->SetXY(10, 85);
                $pdf->writeHTML($classTeacherInvitation['body']);

                // Body date done
                $pdf->Ln(10);
                $pdf->Write(5, "We highly appreciate your usual cooperation.");

                // footer
                $pdf->Ln(15);
                $pdf->Write(5, 'Yours Sincerely.');

                // Moyor
                $pdf->Ln(15);
                $pdf->Write(5, 'Director General: ');
                $pdf->SetFont('Times', 'B', 12);
                $pdf->Write(5, "Dr. MBARUSHIMANA Nelson");

                // footer body
                $pdf->Ln(15);
                $pdf->Write(5, "Cc:
          Head of ICT in Department /REB
          Corporate Services Division Manager /REB");

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
                $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $pdf->write2DBarcode($url, 'QRCODE,Q', 130, 190, 50, 50, $style, 'R');
            }
        }
        $name = "training_letter_Jean_gabin.pdf";
        $pdf->Output($name, 'D');
    }

    private function validateInvitationLetterInput($input)
    {
        if (empty($input['title'])) {
            return ["validated" => false, "message" => "title is required!"];
        }
        if (empty($input['body'])) {
            return ["validated" => false, "message" => "body is required!"];
        }
        if (empty($input['cohort_id'])) {
            return ["validated" => false, "message" => "cohort_id is required!"];
        }
        if (empty($input['letter_type'])) {
            return ["validated" => false, "message" => "letter_type is required!"];
        }
        if (isset($input['letter_type']) && $input['letter_type'] !== "DDE" && $input['letter_type'] !== "Class Teacher" && $input['letter_type'] !== "Head Teacher") {
            return ["validated" => false, "message" => "letter_type is nvalid!, letter_type must be DDE, Head Teacher or Class Teacher"];
        }
        if (isset($input['status']) && $input['status'] != 0 && $input['status'] != 1) {
            return ["validated" => false, "message" => "Invalid status input?, must be 0 or 1"];
        }
        return ["validated" => true, "message" => "OK"];
    }

}
$controller = new InvitationLetterController($this->db, $request_method, $params);
$controller->processRequest();
