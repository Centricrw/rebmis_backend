<?php
namespace Src\System;

use Error;

class SMSHandler
{
    private $sender;
    private $username;
    private $password;

    public function __construct()
    {
        $this->sender = $_ENV['INTOUCHSMS_SENDER_NAME'];
        $this->username = $_ENV['INTOUCHSMS_USERNAME'];
        $this->password = $_ENV['INTOUCHSMS_PASSWORD'];
    }

    public function sendSMSMessage($recipients, $message)
    {
        try {

            if (!isset($this->sender) && !isset($this->username) && !isset($this->password)) {
                throw new Error("SMS Auntacation failed!, please try again?");
            }

            $data = array(
                "sender" => $this->sender,
                "recipients" => $recipients,
                "message" => $message,
            );

            $url = "https://www.intouchsms.co.rw/api/sendsms/.json";
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response = array('result' => json_decode($result, true), 'httpcode' => $httpcode);
            return $response;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }

    public function massageStatusHandler($status)
    {
        switch ($status) {
            case "P":
                return "Processed";
            case "D":
                return "Delivered";
            case "Q":
                return "Queued";
            case "E":
                return "Errored";
            case "S":
                return "Sent";
            case "U":
                return "Unsent/Rejected";
            default:
                return "Invalid";
        }
    }

    public function requestSMSMessageStatus($messageSendId)
    {
        try {

            if (!isset($this->sender) && !isset($this->username) && !isset($this->password)) {
                throw new Error("SMS Auntacation failed!, please try again?");
            }

            $data = array(
                "messageid" => $messageSendId,
            );

            $url = "https://www.intouchsms.co.rw/api/getsmsstatus/.json";
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response = array('result' => var_dump(json_decode($result, true)), 'httpcode' => $httpcode);
            return $response;
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }
}
