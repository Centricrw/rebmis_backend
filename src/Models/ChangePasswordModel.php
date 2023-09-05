<?php
namespace Src\Models;

use Error;

class ChangePasswordModel
{
    private $db = null;
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * update password verfication code
     */
    public function upadetPasswordVerficstionCode($user_id, $verfication_code)
    {
        $updatedQuery = "UPDATE users SET password_verication_code=:password_verication_code
        WHERE user_id=:user_id;";
        try {
            $statement = $this->db->prepare($updatedQuery);
            $statement->execute(array(
                ':password_verication_code' => $verfication_code,
                ':user_id' => $user_id,
            ));
            $result = $statement->rowCount();
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * checking current password verfication code
     */
    public function getCurrentPasswordVerficstionCode($user_id, $verfication_code)
    {
        $selectQuery = "SELECT `password_verication_code` FROM `users` WHERE `user_id`=? AND `password_verication_code`=?";
        try {
            $statement = $this->db->prepare($selectQuery);
            $statement->execute(array($user_id, $verfication_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * function to send message
     *
     */
    public function sendVerficationCodeMessage($msg, $phoneNumber)
    {
        //?Username: university.rwanda, Password: pass1234
        // try {
        //     $data = "";
        //     $url = "https://www.intouchsms.co.rw/api/sendsms/.json";
        //     $data = http_build_query($data);
        //     $username = "university.rwanda";
        //     $password = "pass1234";
        //     $ch = curl_init();
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        //     curl_setopt($ch, CURLOPT_POST, true);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //     $result = curl_exec($ch);
        //     $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //     curl_close($ch);
        //     $response['result'] = $result;
        //     $response['httpcode'] = $httpcode;
        //     return $response;
        // } catch (\Throwable $th) {
        //     throw new Error($th->getMessage());
        // }
    }
}
