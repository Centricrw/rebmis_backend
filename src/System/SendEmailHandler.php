<?php
namespace Src\System;

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use Error;
use PHPMailer\PHPMailer\PHPMailer;

class SendEmailHandler
{
    private $username;
    private $password;
    private $host;

    public function __construct()
    {
        $this->username = $_ENV['SMTP_USERNAME_TEST'];
        $this->password = $_ENV['SMTP_PASSWORD_TEST'];
        $this->host = $_ENV['SMTP_HOST_TEST'];
    }

    public function sendSMSMessage($recipients, $subject, $message)
    {
        try {
            if (!isset($this->host) && !isset($this->username) && !isset($this->password)) {
                throw new Error("Email Authentication failed!, please try again?");
            }

            $mail = new PHPMailer(true);

            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );

            $mail->setFrom($this->username, 'REB MIS assets management');

            $mail->addAddress($recipients);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();

            return 'Email Sent successfully';
        } catch (\Throwable $th) {
            throw new Error($th->getMessage());
        }
    }
}
