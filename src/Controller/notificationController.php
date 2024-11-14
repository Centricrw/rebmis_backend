<?php
namespace Src\Controller;

use Error;
use Src\Models\NotificationModel;
use Src\System\AuthValidation;
use Src\System\DatabaseConnector;
use Src\System\Errors;
use Src\System\SMSHandler;
use Src\System\UuidGenerator;

class NotificationController
{
    private $db;
    private $notificationModel;
    private $request_method;
    private $smsHandler;
    private $params;
    private $closeConnection;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->notificationModel = new NotificationModel($db);
        $this->closeConnection = new DatabaseConnector();
        $this->smsHandler = new SMSHandler();
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['type'] == "sms") {
                    if (isset($this->params['action']) && $this->params['action'] == "receivers") {
                        $response = $this->getSMSMessageReceivers($this->params['message_id']);
                    } elseif (isset($this->params['action']) && $this->params['action'] == "resendsms") {
                        $response = $this->resendSMSMessageToReceiver($this->params['message_id']);
                    } elseif (isset($this->params['action']) && $this->params['action'] == "getsmsstatus") {
                        $response = $this->checkReceiverSMSMessageStatus($this->params['message_id']);
                    } else {
                        $response = $this->getSMSMessages();
                    }
                } else {
                    $response = Errors::notFoundError('Notification route not found');
                }
                break;
            case 'POST':
                if (sizeof($this->params) > 0 && $this->params['type'] == "sms") {
                    if (isset($this->params['action']) && $this->params['action'] == "create") {
                        $response = $this->createNewSMSMessage();
                    } elseif (isset($this->params['action']) && $this->params['action'] == "send") {
                        $response = $this->sendSMSMessageToReceivers();
                    } else {
                        $response = Errors::notFoundError('Notification sms route not found');
                    }
                } else {
                    $response = Errors::notFoundError('Notification route not found');
                }
                break;
            default:
                $response = Errors::notFoundError('Notification route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            $this->closeConnection->closeConnection();
            echo $response['body'];
        }
    }

    // create new sms
    function createNewSMSMessage()
    {
        // Get input data
        $data = json_decode(file_get_contents('php://input'), true);
        $created_by_user_id = AuthValidation::authorized()->id;

        // Validate input if not empty
        $validationInputData = self::validateMessageInput($data);
        if (!$validationInputData['validated']) {
            return Errors::unprocessableEntityResponse($validationInputData['message']);
        }

        try {
            // checking if message title exists
            $messageTitleExists = $this->notificationModel->selectMessageBYTitle($data['messages_title']);
            if (sizeof($messageTitleExists) > 0) {
                return Errors::existError("Message title already exists, please try again?");
            }

            // Generate message id
            $generated_messages_id = UuidGenerator::gUuid();
            $data['messages_id'] = $generated_messages_id;
            $data['created_by'] = $created_by_user_id;
            $result = $this->notificationModel->insertNewSMSMEssage($data);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($data);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // get sms message
    function getSMSMessages()
    {
        $created_by_user_id = AuthValidation::authorized()->id;

        try {
            $messages = $this->notificationModel->selectMessageBYCreator($created_by_user_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($messages);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function sendSMSMessageHandler($message, $receiver)
    {
        try {
            $messageBody = "Dear " . $receiver['full_name'] . "\n" . $message['messages_body'] . "\n" . $message['send_by'] . "/" . "REBMIS";
            $sendSms = $this->smsHandler->sendSMSMessage($receiver['phone_number'], $messageBody);
            $SMSResponse = $sendSms['result'];

            // checking if there is error
            $httpStatusCode = $sendSms['httpcode'];
            if ($httpStatusCode != 200 || !isset($SMSResponse['details'])) {
                $error = isset($SMSResponse["response"][0]["errors"]) ? $SMSResponse["response"][0]["errors"] : [
                    "action" => "Cannot send messages.",
                    "error" => "Failed to send SMS Message",
                ];
                throw new Error($error['action'] . " " . $error['error'] . ", please contact administrator ?");
            }

            // handling results
            $responseDetails = $SMSResponse['details'][0];
            $messageStatus = $this->smsHandler->massageStatusHandler($responseDetails['status']);
            // update user
            $data = [
                "messages_receivers_id" => $receiver['messages_receivers_id'],
                "messages_id" => $message['messages_id'],
                "full_name" => $receiver['full_name'],
                "email" => $receiver['email'],
                "phone_number" => $receiver['phone_number'],
                "messages_send_id" => $responseDetails['messageid'],
                "messages_send_success" => $SMSResponse['success'] ? true : false,
                "messages_send_status" => $messageStatus,
                "status" => 1,
            ];
            $this->notificationModel->updateMessageRecievers($data);
            return $data;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    // send sms message
    function sendSMSMessageToReceivers()
    {
        // Get input data
        $data = json_decode(file_get_contents('php://input'), true);
        $created_by_user_id = AuthValidation::authorized()->id;

        // Validate input if not empty
        $validationInputData = self::validateSentReceiversInput($data);
        if (!$validationInputData['validated']) {
            return Errors::unprocessableEntityResponse($validationInputData['message']);
        }

        try {
            // save new receivers
            foreach ($data['receivers'] as $key => $item) {
                // checking if receiver exists
                $receiverExists = $this->notificationModel->selectOneMessageReceivers($data['messages_id'], $item['phone_number']);
                if (sizeof($receiverExists) == 0) {
                    // Generate message receiver id
                    $generated_messages_receivers_id = UuidGenerator::gUuid();
                    $item['messages_receivers_id'] = $generated_messages_receivers_id;
                    $item['messages_id'] = $data['messages_id'];
                    $result = $this->notificationModel->insertNewMessageRecievers($item);
                }
            }

            // send sms to receiver
            $message = $this->notificationModel->selectMessageBYId($data['messages_id']);
            $receivers = $this->notificationModel->selectMessageReceiversBYMessageId($data['messages_id']);
            if (sizeof($message) > 0 && sizeof($receivers) > 0) {
                // send sms receivers
                foreach ($receivers as $key => $item) {
                    // cecking if receiver alredy received sms
                    if (!isset($item['messages_send_id'])) {
                        $this->sendSMSMessageHandler($message[0], $item);
                    }
                }
            }

            $result = $this->notificationModel->selectMessageReceiversBYMessageId($data['messages_id']);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // get sms receivers
    function getSMSMessageReceivers($message_id)
    {
        $created_by_user_id = AuthValidation::authorized()->id;
        try {
            $result = $this->notificationModel->selectMessageReceiversBYMessageId($message_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // resend sms to receiver
    function checkReceiverSMSMessageStatus($messages_receivers_id)
    {
        $created_by_user_id = AuthValidation::authorized()->id;
        try {
            $receiver = $this->notificationModel->selectOneMessageReceiversBYId($messages_receivers_id);
            if (sizeof($receiver) == 0) {
                return Errors::notFoundError("Receiver details not found!, plase try again?");
            }
            $receiverDetails = $receiver[0];
            // checcking sms status
            $sendSms = $this->smsHandler->requestSMSMessageStatus($receiverDetails['messages_send_id']);
            $SMSResponse = $sendSms['result'];
            // checking if there is error
            $httpStatusCode = $sendSms['httpcode'];
            if ($httpStatusCode != 200 || !isset($SMSResponse['results'])) {
                $error = isset($SMSResponse["response"][0]["errors"]) ? $SMSResponse["response"][0]["errors"] : [
                    "action" => "Cannot get messages status.",
                    "error" => "Failed to Get Status",
                ];
                throw new Error($error['action'] . " " . $error['error'] . ", please contact administrator ?");
            }

            // handling results
            $responseDetails = $SMSResponse['results'];
            $messageStatus = $this->smsHandler->massageStatusHandler($responseDetails['status']);
            // update user
            $data = [
                "messages_receivers_id" => $receiverDetails['messages_receivers_id'],
                "messages_id" => $receiverDetails['messages_id'],
                "full_name" => $receiverDetails['full_name'],
                "email" => $receiverDetails['email'],
                "phone_number" => $receiverDetails['phone_number'],
                "messages_send_id" => $receiverDetails['messages_send_id'],
                "messages_send_success" => $SMSResponse['success'] ? true : false,
                "messages_send_status" => $messageStatus,
                "status" => 1,
            ];

            $this->notificationModel->updateMessageRecievers($data);
            $result = $this->notificationModel->selectOneMessageReceiversBYId($messages_receivers_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // resend sms to receiver
    function resendSMSMessageToReceiver($messages_receivers_id)
    {
        $created_by_user_id = AuthValidation::authorized()->id;
        try {
            $receiver = $this->notificationModel->selectOneMessageReceiversBYId($messages_receivers_id);
            if (sizeof($receiver) == 0) {
                return Errors::notFoundError("Receiver details not found!, plase try again?");
            }
            // send sms to receiver
            $message = $this->notificationModel->selectMessageBYId($receiver[0]['messages_id']);
            if (sizeof($message) > 0) {
                // send sms receivers
                $this->sendSMSMessageHandler($message[0], $receiver[0]);
            }
            $result = $this->notificationModel->selectOneMessageReceiversBYId($messages_receivers_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function validateMessageInput($input)
    {
        if (empty($input['messages_title'])) {
            return ["validated" => false, "message" => "messages_title is not provided!"];
        }
        if (empty($input['messages_body'])) {
            return ["validated" => false, "message" => "messages_body is not provided!"];
        }
        if (!isset($input['send_by'])) {
            return ["validated" => false, "message" => "send_by is not provided!"];
        }
        if (empty($input['message_type'])) {
            return ["validated" => false, "message" => "message_type must be SMS or WhatsApp"];
        }
        return ["validated" => true, "message" => "OK"];
    }

    private function validateSentReceiversInput($input)
    {
        if (empty($input['messages_id'])) {
            return ["validated" => false, "message" => "messages_id is not provided!"];
        }
        if (empty($input['receivers']) || !is_array($input['receivers'])) {
            return ["validated" => false, "message" => "receivers must be array!"];
        }
        if (is_array($input['receivers'])) {
            foreach ($input['receivers'] as $key => $item) {
                // Validate receiver input
                $validatieReceiversInput = self::validateReceiverInput($item);
                if (!$validatieReceiversInput['validated']) {
                    return ["validated" => false, "message" => $validatieReceiversInput['message']];
                }
            }
        }
        return ["validated" => true, "message" => "OK"];
    }

    private function validateReceiverInput($input)
    {
        if (empty($input['full_name'])) {
            return ["validated" => false, "message" => "full_name is not provided!"];
        }

        // Validate phone number
        if (!isset($input["phone_number"]) || !is_string($input["phone_number"]) || strlen($input["phone_number"]) != 10 || !preg_match('/^07/', $input["phone_number"])) {
            return ["validated" => false, "message" => "This " . $input["phone_number"] . " Phone number must be starting with '07' and have 10 digits"];
        }
        return ["validated" => true, "message" => "OK"];
    }
}

$controller = new NotificationController($this->db, $request_method, $params);
$controller->processRequest();
