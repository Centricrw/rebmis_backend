<?php
namespace Src\Controller;

use DateTime;
use setasign\Fpdi\Tcpdf\Fpdi;
use Src\Models\AssetsDistributionModel;
use Src\Models\AssetsModel;
use Src\Models\LocationsModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class DeliveryNoteController
{
    private $db;
    private $assetsModel;
    private $assetsDistributionModel;
    private $request_method;
    private $locationsModel;
    private $homeDir;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->assetsModel = new AssetsModel($db);
        $this->assetsDistributionModel = new AssetsDistributionModel($db);
        $this->locationsModel = new LocationsModel($db);
        $this->homeDir = dirname(__DIR__, 2);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if ($this->params['action'] == "received") {
                    $response = $this->generateReceivedNoteForReb();
                } else {
                    $response = $this->generateDeliveryNoteForSchool();
                }
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

    private function generateDeliveryNoteForSchool()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        //
        try {
            $results = [];
            // get address
            $province = $this->locationsModel->getAddressDetails(substr($data['school_code'], 0, 1), "provinces");
            $district = $this->locationsModel->getAddressDetails(substr($data['school_code'], 0, 2), "districts");
            $sector = $this->locationsModel->getAddressDetails(substr($data['school_code'], 0, 4), "sectors");
            $school = $this->locationsModel->getAddressDetails($data['school_code'], "schools");

            if (count($school) === 0) {
                return Errors::badRequestError("School not found, please try again?");
            }

            if (!isset($data['note'])) {
                return Errors::badRequestError("Note not found!, please try again?");
            }

            // get batch details
            $batchDetails = $this->assetsDistributionModel->selectBatchDetailsByBatchID($data['batch_id']);
            if (count($batchDetails) === 0) {
                return Errors::badRequestError("Batch Details not found, please try again?");
            }
            foreach ($batchDetails as $key => $value) {
                $assets = $this->assetsModel->selectAssetsForBatchOnShool($data['school_code'], $value['id']);
                $results = array_merge($results, $assets);
            }

            if (count($results) > 0) {
                return $this->createDeliveryNotePdfForSchool($results, $data['note'], [
                    "province" => $province[0],
                    "district" => $district[0],
                    "sector" => $sector[0],
                    "school" => $school[0],
                ]);
            }

            // $response['status_code_header'] = 'HTTP/1.1 200 OK';
            // $response['body'] = json_encode($results);
            // return $response;

            return Errors::badRequestError("This batch on school assets not found!, please try again?");
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function is_valid_date($date_string, $format = 'Y-m-d')
    {
        $date_obj = DateTime::createFromFormat($format, $date_string);
        return $date_obj && $date_obj->format($format) === $date_string;
    }

    private function generateReceivedNoteForReb()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        //"date": "",
        // "supplier_name": "",
        // "note": ""
        try {
            if (!isset($data['note'])) {
                return Errors::badRequestError("Note not found!, please try again?");
            }
            if (!isset($data['date']) || !$this->is_valid_date($data['date'])) {
                return Errors::badRequestError("Date not found or invalid!, please try again?");
            }
            if (!isset($data['supplier_name'])) {
                return Errors::badRequestError("Supplier Name not found!, please try again?");
            }

            $assets = $this->assetsModel->selectAssetsReceivedOnRebBYDate($data['date'], $data['supplier_name']);

            if (count($assets) > 0) {
                return $this->createReceivedNotePDF($assets, $data['note'], $data['supplier_name'], $data['date']);
            }

            // $response['status_code_header'] = 'HTTP/1.1 200 OK';
            // $response['body'] = json_encode($results);
            // return $response;

            return Errors::badRequestError("Assets not found!, please try again?");
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    public function createReceivedNotePDF($assetsOnSchool, $note, $surlier_name, $inputDate)
    {
        // create new PDF document
        $inputDateTimeStamp = strtotime($inputDate);
        $date = date("d F Y", $inputDateTimeStamp);
        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('REB');
        $pdf->SetTitle('Received note for reb');
        $pdf->SetSubject('Received note');
        $pdf->SetKeywords('REB, PDF, TCPDF');

        // remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set auto page break and bottom margin to zero
        $pdf->setAutoPageBreak(true, 0);

        // ---------------------------------------------------------
        $pdf->startPageGroup();
        // create new page
        $pdf->AddPage();

        // Set the template file
        $template = $this->homeDir . '/public/delivery_note_template_A4.pdf';

        // Add a page using the template
        $pdf->setSourceFile($template);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0);

        // adding Date
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetXY(10, 40);
        $pdf->Ln();
        $pdf->Cell(190, 5, "Kigali on: $date", 0, 1, 'R');
        $pdf->Ln();

        // adding title
        $title_text = "RECEIVED NOTE";
        // Calculate title width and center position
        $pdf->SetFont('Times', 'BU', 16);
        $title_width = $pdf->GetStringWidth($title_text);
        $page_width = $pdf->GetPageWidth();
        $center_x = ($page_width - $title_width) / 2;

        // Set position and write title
        $pdf->SetXY($center_x, 50);
        $pdf->Write(5, $title_text);

        // adding assets on school table
        // Column headings
        $pdf->SetXY(10, 65);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Cell(10, 6, 'NO', 1, 0, 'C', 0);
        $pdf->Cell(50, 6, 'ASSETS DESCRIPTION', 1, 0, 'C', 0);
        $pdf->Cell(50, 6, 'S/N', 1, 0, 'C', 0); // New line after header
        $pdf->Cell(30, 6, 'CONDITION', 1, 0, 'C', 0); // New line after header
        $pdf->Cell(50, 6, 'SUPPLIER NAME', 1, 1, 'C', 0); // New line after header

        // Table data
        $pdf->SetFont('Times', '', 10);
        $data = array();
        // setting data
        foreach ($assetsOnSchool as $key => $value) {
            array_push($data, [$key + 1, $value['assets_categories_name'] . ", " . $value['assets_sub_categories_name'], $value['serial_number'], $value['condition'], $surlier_name]);
        }

        $estimated_table_height = 50;

        foreach ($data as $row) {
            // Check for page break and add new page if necessary
            if ($pdf->GetY() + $estimated_table_height > $pdf->GetPageHeight()) {
                $pdf->AddPage();
                $pdf->useTemplate($tplIdx, 0, 0); // Apply template again
                // Reset Y position after adding a new page
                $pdf->SetY(50); // Adjust Y position as needed for new page content
            }
            foreach ($row as $cell) {
                if (is_numeric($cell)) {
                    $pdf->Cell(10, 6, $cell, 1, 0, 'L');
                } else if ($cell === "GOOD" || $cell === "OBSOLETE" || $cell === "BAD") {
                    $pdf->Cell(30, 6, $cell, 1, 0, 'L');
                } else {
                    $pdf->Cell(50, 6, $cell, 1, 0, 'L');
                }
            }
            $pdf->Ln(); // New line after each row
        }

        $current_y = $pdf->GetY();
        $page_height = $pdf->GetPageHeight();
        $auto_page_break = $pdf->getAutoPageBreak();

        if (($current_y + 60 > $page_height) && $auto_page_break) {
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0); // Apply template again
            // Reset Y position after adding a new page
            $pdf->SetY(50); // Adjust Y position as needed for new page con
            // adding footer paragraph
            $pdf->SetFont('Times', '', 12);
            $textHeader = "NOTE: " . $note;
            $pdf->MultiCell(165, 13, $textHeader, 0, 'L', false, 1, 10, 50);

            $pdf->SetXY(10, 50 + 20);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Write(5, "Good received in good order by: ", '', false, '', true);
            $pdf->Write(5, "Signature and stamp", '', false, '', true);
            $pdf->Write(5, "Handed over by: ", '', false, '', true);
            $pdf->Write(5, "Signature", '', false, '', true);

        } else {
            // adding footer paragraph
            $pdf->SetFont('Times', '', 12);
            $textHeader = "NOTE: " . $note;
            $pdf->MultiCell(165, 13, $textHeader, 0, 'L', false, 1, 10, $current_y + 5);

            $pdf->SetXY(10, $current_y + 20);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Write(5, "Good received in good order by: ", '', false, '', true);
            $pdf->Write(5, "Signature and stamp", '', false, '', true);
            $pdf->Write(5, "Handed over by: ", '', false, '', true);
            $pdf->Write(5, "Signature", '', false, '', true);

        }

        // Close and output PDF document
        $pdf->Output('delivery_note_on_school.pdf', 'D');
    }

    public function createDeliveryNotePdfForSchool($assetsOnSchool, $note, $address)
    {
        // create new PDF document
        $date = date("d F Y");
        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('REB');
        $pdf->SetTitle('Delivery note on school');
        $pdf->SetSubject('Delivery note');
        $pdf->SetKeywords('REB, PDF, TCPDF');

        // remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set auto page break and bottom margin to zero
        $pdf->setAutoPageBreak(true, 0);

        // ---------------------------------------------------------
        $pdf->startPageGroup();
        // create new page
        $pdf->AddPage();

        // Set the template file
        $template = $this->homeDir . '/public/delivery_note_template_A4.pdf';

        // Add a page using the template
        $pdf->setSourceFile($template);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0);

        // adding Date
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetXY(10, 40);
        $pdf->Ln();
        $pdf->Cell(190, 5, "Kigali on: $date", 0, 1, 'R');
        $pdf->Ln();

        // adding address
        $pdf->SetFont('Times', '', 14);
        $pdf->SetXY(10, 50);
        $pdf->Write(1, "PROVINCE: " . $address['province']['provincename'], '', false, '', true);
        $pdf->Write(1, "DISTRICT: " . $address['district']['namedistrict'], '', false, '', true);
        $pdf->Write(1, "SECTOR: " . $address['sector']['namesector'], '', false, '', true);
        $pdf->Write(1, "SCHOOL NAME: " . $address['school']['school_name'], '', false, '', true);
        $pdf->Ln();

        // adding title
        $title_text = "DELIVERY NOTE";
        // Calculate title width and center position
        $pdf->SetFont('Times', 'BU', 16);
        $title_width = $pdf->GetStringWidth($title_text);
        $page_width = $pdf->GetPageWidth();
        $center_x = ($page_width - $title_width) / 2;

        // Set position and write title
        $pdf->SetXY($center_x, 80);
        $pdf->Write(5, $title_text);

        // adding assets on school table
        // Column headings
        $pdf->SetXY(10, 95);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Cell(10, 6, 'NO', 1, 0, 'C', 0);
        $pdf->Cell(50, 6, 'ASSETS DESCRIPTION', 1, 0, 'C', 0);
        $pdf->Cell(50, 6, 'ASSETS CODE', 1, 0, 'C', 0); // New line after header
        $pdf->Cell(50, 6, 'S/N', 1, 0, 'C', 0); // New line after header
        $pdf->Cell(30, 6, 'CONDITION', 1, 1, 'C', 0); // New line after header

        // Table data
        $pdf->SetFont('Times', '', 10);
        $data = array();
        // setting data
        foreach ($assetsOnSchool as $key => $value) {
            array_push($data, [$key + 1, $value['assets_categories_name'] . ", " . $value['assets_sub_categories_name'], $value['assets_tag'], $value['serial_number'], $value['condition']]);
        }

        $estimated_table_height = 50;

        foreach ($data as $row) {
            // Check for page break and add new page if necessary
            if ($pdf->GetY() + $estimated_table_height > $pdf->GetPageHeight()) {
                $pdf->AddPage();
                $pdf->useTemplate($tplIdx, 0, 0); // Apply template again
                // Reset Y position after adding a new page
                $pdf->SetY(50); // Adjust Y position as needed for new page content
            }
            foreach ($row as $cell) {
                if (is_numeric($cell)) {
                    $pdf->Cell(10, 6, $cell, 1, 0, 'L');
                } else if ($cell === "GOOD" || $cell === "OBSOLETE" || $cell === "BAD") {
                    $pdf->Cell(30, 6, $cell, 1, 0, 'L');
                } else {
                    $pdf->Cell(50, 6, $cell, 1, 0, 'L');
                }
            }
            $pdf->Ln(); // New line after each row
        }

        $current_y = $pdf->GetY();
        $page_height = $pdf->GetPageHeight();
        $auto_page_break = $pdf->getAutoPageBreak();

        if (($current_y + 60 > $page_height) && $auto_page_break) {
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0); // Apply template again
            // Reset Y position after adding a new page
            $pdf->SetY(50); // Adjust Y position as needed for new page con
            // adding footer paragraph
            $pdf->SetFont('Times', '', 12);
            $textHeader = "NOTE: " . $note;
            $pdf->MultiCell(165, 13, $textHeader, 0, 'L', false, 1, 10, 50);

            $pdf->SetXY(10, 50 + 20);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Write(5, "Good received in good order by: ", '', false, '', true);
            $pdf->Write(5, "Signature and stamp", '', false, '', true);
            $pdf->Write(5, "Handed over by: ", '', false, '', true);
            $pdf->Write(5, "Signature", '', false, '', true);

        } else {
            // adding footer paragraph
            $pdf->SetFont('Times', '', 12);
            $textHeader = "NOTE: " . $note;
            $pdf->MultiCell(165, 13, $textHeader, 0, 'L', false, 1, 10, $current_y + 5);

            $pdf->SetXY(10, $current_y + 20);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Write(5, "Good received in good order by: ", '', false, '', true);
            $pdf->Write(5, "Signature and stamp", '', false, '', true);
            $pdf->Write(5, "Handed over by: ", '', false, '', true);
            $pdf->Write(5, "Signature", '', false, '', true);

        }

        // Close and output PDF document
        $pdf->Output('delivery_note_on_school.pdf', 'D');
    }

}
$controller = new DeliveryNoteController($this->db, $request_method, $params);
$controller->processRequest();
