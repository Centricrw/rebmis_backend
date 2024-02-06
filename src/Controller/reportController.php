<?php
namespace Src\Controller;

use Src\Models\ReportModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class reportController
{
    private $db;
    private $reportModel;
    private $userRoleModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->reportModel = new ReportModel($db);
        $this->userRoleModel = new UserRoleModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "getAll") {
                    $response = $this->getGeneralReport();
                } 
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerSchool") {
                    $response = $this->getGeneralReport($this->params['schoolCode']);
                } 
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerTraining") {
                    $response = $this->getGeneralReportPerTraining($this->params['id']);
                } 
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerTrainee") {
                    $response = $this->getGeneralReportPerTrainee($this->params['id']);
                }
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "generatePdfCertificate") {
                    $response = $this->generatePdfCertificate($this->params['id']);
                } else {
                    $response = Errors::notFoundError('Report route not found');
                }
                break;
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "mark") {
                    $response = $this->markTheTrainee();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "headteacher") {
                    $response = $this->headTeacherTraineeMarkhandler();
                } else {
                    $response = Errors::notFoundError('Report route not found');
                }
                break;
            default:
                $response = Errors::notFoundError('Report route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getGeneralReport()
    {
        $result = $this->reportModel->getGeneralReport();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getGeneralReportPerTraining($training_id)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = sizeof($current_user_role) > 0 ? $current_user_role[0] : [];
            $school = isset($user_role_details['school_code']) ? $user_role_details['school_code'] : null;

            // if logged user is head teacher then return genaral report from school only
            if (isset($school) && !empty($school) && $user_role_details['role_id'] == "2") {
                $result = $this->reportModel->getGeneralReportPerTrainingForSchool($training_id, $school);
            } else {
                $result = $this->reportModel->getGeneralReportPerTraining($training_id);
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getGeneralReportPerTrainee($staff_code)
    {
        try {
            $result = $this->reportModel->getGeneralReportPerTrainee($staff_code);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function markTheTrainee()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->reportModel->markTheTrainee($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function headTeacherTraineeMarkhandler()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = $current_user_role[0];

            // checking if is headteacher logged in
            if ($user_role_details['role_id'] != "2") {
                return Errors::badRequestError("Logged user is not head teacher, please try gain?");
            }

            $result = $this->reportModel->headTeacherTraineeMarking($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function generatePdfCertificate($staff_code)
    {
        try {
            $teacherDetails = $this->reportModel->getGeneralReportPerTrainee($staff_code);
            $this->genarateReportPDF($teacherDetails);
            return $teacherDetails;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
        
    }

    public function genarateReportPDF($teacherDetails)
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
        $pdf->Write(1, 'Certificate', '', false, '', true);
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

}
$controller = new reportController($this->db, $request_method, $params);
$controller->processRequest();
