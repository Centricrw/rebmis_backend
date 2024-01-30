<?php
namespace Src\Models;

use Error;

class NotificationModel
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create new message
     * @param OBJECT $data
     * @return VOID
     */
    public function insertNewSMSMEssage($data)
    {
        $statement = "INSERT INTO `messages`(`messages_id`, `messages_title`, `messages_body`, `send_by`, `message_type`, `created_by`) VALUES (:messages_id, :messages_title, :messages_body, :send_by, :message_type, :created_by)";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":messages_id" => $data['messages_id'],
                ":messages_title" => $data['messages_title'],
                ":messages_body" => $data['messages_body'],
                ":send_by" => $data['send_by'],
                ":message_type" => $data['message_type'],
                ":created_by" => $data['created_by'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new message
     * @param STRING $messageTitle
     * @return OBJECT
     */
    public function selectMessageBYTitle($messageTitle)
    {
        $statement = "SELECT * FROM `messages` WHERE `messages_title` = :messages_title LIMIT 1";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":messages_title" => $messageTitle));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select sms message by user
     * @param STRING $createdBY
     * @return OBJECT
     */
    public function selectMessageBYCreator($createdBY)
    {
        $statement = "SELECT * FROM `messages` WHERE `created_by` = :created_by";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":created_by" => $createdBY));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new message
     * @param STRING $messageId
     * @return OBJECT
     */
    public function selectMessageBYId($messageId)
    {
        $statement = "SELECT * FROM `messages` WHERE `messages_id` = :messages_id LIMIT 1";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":messages_id" => $messageId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Create new message
     * @param OBJECT $data
     * @return VOID
     */
    public function insertNewMessageRecievers($data)
    {
        $statement = "INSERT INTO `messages_receivers`(`messages_receivers_id`, `messages_id`, `full_name`, `email`, `phone_number`) VALUES (:messages_receivers_id, :messages_id, :full_name, :email, :phone_number)";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":messages_receivers_id" => $data['messages_receivers_id'],
                ":messages_id" => $data['messages_id'],
                ":full_name" => $data['full_name'],
                ":email" => $data['email'],
                ":phone_number" => $data['phone_number'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * Update message receivers
     * @param OBJECT $data
     * @return VOID
     */
    public function updateMessageRecievers($data)
    {
        $statement = "UPDATE `messages_receivers` SET `messages_id`=:messages_id, `full_name`=:full_name,`email`=:email,`phone_number`=:phone_number,`messages_send_id`=:messages_send_id,`messages_send_success`=:messages_send_success,`messages_send_status`=:messages_send_status,`status`=:status WHERE `messages_receivers_id`= :messages_receivers_id";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":messages_receivers_id" => $data['messages_receivers_id'],
                ":messages_id" => $data['messages_id'],
                ":full_name" => $data['full_name'],
                ":email" => $data['email'],
                ":phone_number" => $data['phone_number'],
                ":messages_send_id" => isset($data['messages_send_id']) ? $data['messages_send_id'] : null,
                ":messages_send_success" => isset($data['messages_send_success']) ? $data['messages_send_success'] : false,
                ":messages_send_status" => isset($data['messages_send_status']) ? $data['messages_send_status'] : null,
                ":status" => $data['status'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select message receiver
     * @param STRING $messages_id
     * @return OBJECT
     */
    public function selectMessageReceiversBYMessageId($messages_id)
    {
        $statement = "SELECT * FROM `messages_receivers` WHERE `messages_id` = :messages_id";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":messages_id" => $messages_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select one message receiver
     * @param STRING $messages_id
     * @param STRING $phone_number
     * @return OBJECT
     */
    public function selectOneMessageReceivers($messages_id, $phone_number)
    {
        $statement = "SELECT * FROM `messages_receivers` WHERE `messages_id` = :messages_id AND `phone_number` = :phone_number LIMIT 1 ";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":messages_id" => $messages_id, ":phone_number" => $phone_number));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * select one message receiver by id
     * @param STRING $messages_receivers_id
     * @return OBJECT
     */
    public function selectOneMessageReceiversBYId($messages_receivers_id)
    {
        $statement = "SELECT * FROM `messages_receivers` WHERE `messages_receivers_id` = :messages_receivers_id LIMIT 1 ";
        try {
            // Remove whitespaces from both sides of a string
            $statement = $this->db->prepare($statement);
            $statement->execute(array(":messages_receivers_id" => $messages_receivers_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
