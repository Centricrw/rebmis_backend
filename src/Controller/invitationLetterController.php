<?php
namespace Src\Controller;

use Src\Models\InvitationLetterModel;
use Src\Models\LocationsModel;
use Src\Models\TrainingsModel;
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
    private $locationsModel;
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
        $this->locationsModel = new LocationsModel($db);
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
                    $response = $this->genarateSuspensionLetter($this->params['id']);
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
        $trainingExists = $this->trainingsModel->getOneTraining($data['trainingId']);
        if (sizeof($trainingExists) == 0) {
            return Errors::notFoundError("Training id not found, please try again?");
        }
        // Generate training center id
        $generated_invitation_tammplete_id = UuidGenerator::gUuid();
        $data['id'] = $generated_invitation_tammplete_id;
        try {
            // checking if letter exists for that training
            $invitationTammpleteExists = $this->invitationLetterModel->selectInvintationLetterByType($data['trainingId'], $data['letter_type']);
            if (sizeof($invitationTammpleteExists) > 0) {
                return Errors::badRequestError("This invitation letter already exists, please try again?");
            }
            $this->invitationLetterModel->insertNewInvitationTammplete($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "id" => $data['id'],
                "title" => $data['title'],
                "body" => $data['body'],
                "trainingId" => $data['trainingId'],
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
     * @param {STRING} training_id
     * @return {OBJECT} {results}
     */
    public function getAllInvintationLetterAssignedToTraining($training_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->invitationLetterModel->selectInvintationLetterByTraining($training_id);
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

        if ($invitationLetterExists[0]['trainingId'] !== $data['trainingId']) {
            // checking if training id exists
            $trainingExists = $this->trainingsModel->getOneTraining($data['trainingId']);
            if (sizeof($trainingExists) == 0) {
                return Errors::notFoundError("Training id not found, please try again?");
            }

            // checking if letter exists for that training
            $invitationTammpleteExists = $this->invitationLetterModel->selectInvintationLetterByType($data['trainingId'], $data['letter_type']);
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
                "trainingId" => $data['trainingId'],
                "letter_type" => $data['letter_type'],
                "status" => $data['status'],
                "message" => "Invitation letter tamplete  updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // suspension letter Handler
    public function genarateSuspensionLetter($user_id)
    {
        // SUSPENDED
        $userResult = $this->usersModel->findOneUser($user_id);
        $schoolCode = "110101";

        // checking if school is provided
        if (empty($schoolCode)) {
            $response = Errors::notFoundError("User School Not Found, Please Try Again Leter?");
            return $response;
        }
        $districtCode = $userResult[0]['district_code'] ? $userResult[0]['district_code'] : substr($schoolCode, 0, 2);
        $provinceCode = $userResult[0]['province_code'];
        $provinceResults = $this->locationsModel->getAddressDetails($provinceCode, "provinces");
        $destrictResults = $this->locationsModel->getAddressDetails($districtCode, "districts");

        // needed valiables
        $province = "Kigali";
        $district = "Nyarugenge";
        $ref = "TMIS/Suspension No: 0" . 11;
        $action = "Training";
        $subject = "Subject";
        $userName = $userResult[0]['full_name'];
        $schoolName = "ECD RWAKIVUMU";
        $role = "class teacher";
        $statrtDate = ". . / . . / . . . .";
        $endDate = ". . / . . / . . . .";

        $reasonMessage = "Training invitation letter";
        $messageBody = "First, I want to introduce myself. Flora Patty, the senior account manager. I am writing this letter to you to invite you to our upcoming four-day optional training program for all sales department employees. This training program is designed to inculcate additional selling skills and strategies among the sales team and we hope that you would be paying attention to it.This training program also included a marketing seminar for the participants.";

        $reason = "Since this is not the obligatory training program, not attending it wouldn’t be marked as a negative against you. But it would be my personal appeal to try and make it to the program as it would be very helpful for you.  To register, kindly get and fill out a form from the accounts department front desk and submit it within 2 days. If your application isn’t received in this period, we wouldn’t be able to lodge you. Looking forward to your training application,";
        $effectImediate = "This suspension is effective from the date it is signed.";
        $mayor = "Sebutege Ange";
        // $response['status_code_header'] = 'HTTP/1.1 200 OK';
        // $response['body'] = json_encode($userResult);

        return $this->createPDF($province, $district, $ref, $action, $userName, $messageBody, $reason, $mayor, $schoolName, $effectImediate, $subject);
    }

    public function createPDF($province, $district, $ref, $action, $userName, $messageBody, $reason, $mayor, $schoolName, $effectImediate = "", $subject = "Subject")
    {
        //declaration

        // reference: TMIS/Appointment No: 00001
        // action: Termination, Transfer, Suspension and Appointment
        // subject: Re: or Subject:
        // name of the letter user
        // message body

        $address = $province == "Kigali City" ? $province : $district;

        //end
        $date = date("d F Y");
        // initiate FPDI
        $pdf = new TCPDF();
        // add a page
        $pdf->AddPage('P', 'A4', true);

        // adding Date
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY(10, 30);
        $pdf->Ln();
        $pdf->Cell(190, 5, "Done on: $date", 0, 1, 'R');
        $pdf->Cell(190, 5, "Ref: $ref", 0, 0, 'R');
        $pdf->Ln();

        // add top header text
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(10, 10);
        $pdf->Write(10, 'REPUBLIC OF RWANDA');

        // adding logo
        $pdf->Image($this->homeDir . '/public/logos/Rwanda_national_emblem.png', 20, 20, 40);
        // adding address
        $pdf->SetFont('Times', 'B', 12);
        $pdf->SetXY(10, 65);
        $pdf->Write(10, "$province PROVINCE");
        $pdf->Ln();
        $pdf->Write(5, "$district DISTRICT");

        // adding letter title
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetXY(10, 85);
        // use Re: for request and Subject for inforation
        $pdf->Write(10, $subject . ': ' . $action . ' Letter');

        // body
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY(10, 100);
        $pdf->Write(7, "Dear $userName,");
        $pdf->Ln();
        $pdf->writeHTML($messageBody);

        // Body reason
        $pdf->Ln();
        $pdf->writeHTML($reason);

        // Body date done
        $pdf->Ln(10);
        $pdf->Write(5, $effectImediate);

        // footer
        $pdf->Ln(15);
        $pdf->Write(5, 'Yours Sincerely.');

        // Moyor
        $pdf->Ln(15);
        $pdf->Write(5, 'Mayor: ');
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Write(5, $mayor);
        $pdf->Ln();
        $pdf->SetFont('Times', '', 12);
        $pdf->Write(5, "$district District");

        // footer body
        $pdf->Ln(15);
        $pdf->Write(5, "Cc:
            Minister of Education
            Minister of Public Service and Labor
            Executive Secretary of Public Service Commission
            Director General of Rwanda Education
            Board Governor/Mayor of $address
            Head Teacher of $schoolName");

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
        $name = "$action Letter for $userName.pdf";
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
        if (empty($input['trainingId'])) {
            return ["validated" => false, "message" => "trainingId is required!"];
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
