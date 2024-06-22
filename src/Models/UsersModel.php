<?php
namespace Src\Models;

use Error;

class UsersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function insertNewUser($data)
    {
        $query = "INSERT INTO `users` (`staff_code`, `staff_category_id`, `full_name`, `sex`, `dob`, `marital_status`, `nid`, `email`, `phone_numbers`, `rssb_number`, `education_domain_id`, `education_sub_dommain_id`, `graduation_date`, `highest_qualification_id`, `hired_date`, `contract_type`, `bank_account`, `nationality_id`, `user_id`, `username`, `password`, `created_by`, `first_name`, `middle_name`, `last_name`, `specialization_id`, `resident_district_id`) VALUES (:staff_code, :staff_category_id, :full_name, :sex, :dob, :marital_status, :nid, :email, :phone_numbers, :rssb_number, :education_domain_id, :education_sub_dommain_id, :graduation_date, :highest_qualification_id, :hired_date, :contract_type, :bank_account, :nationality_id, :user_id, :username, :password, :created_by, :first_name, :middle_name, :last_name, :specialization_id, :resident_district_id);";
        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                ':staff_code' => isset($data['staff_code']) && !empty($data['staff_code']) ? $data['staff_code'] : $data['user_id'],
                ':staff_category_id' => empty($data['staff_category_id']) ? null : $data['staff_category_id'],
                ':full_name' => $data['full_name'],
                ':sex' => $data['gender'],
                ':dob' => empty($data['dob']) ? null : $data['dob'],
                ':marital_status' => empty($data['marital_status']) ? null : $data['marital_status'],
                ':nid' => $data['nid'],
                ':email' => $data['email'],
                ':phone_numbers' => $data['phone_numbers'],
                ':rssb_number' => empty($data['rssb_number']) ? null : $data['rssb_number'],
                ':education_domain_id' => empty($data['education_domain_id']) ? null : $data['education_domain_id'],
                ':education_sub_dommain_id' => empty($data['education_sub_dommain_id']) ? null : $data['education_sub_dommain_id'],
                ':graduation_date' => empty($data['graduation_date']) ? null : $data['graduation_date'],
                ':highest_qualification_id' => empty($data['highest_qualification_id']) ? null : $data['highest_qualification_id'],
                ':hired_date' => empty($data['hired_date']) ? null : $data['hired_date'],
                ':contract_type' => empty($data['contract_type']) ? null : $data['contract_type'],
                ':bank_account' => empty($data['bank_account']) ? null : $data['bank_account'],
                ':nationality_id' => empty($data['nationality_id']) ? 1 : $data['nationality_id'],
                ':user_id' => $data['user_id'],
                ':username' => isset($data['username']) && !empty($data['username']) ? $data['username'] : $data['phone_numbers'],
                ':password' => $data['password'],
                ':created_by' => $data['created_by'],
                ':first_name' => $data['first_name'],
                ':middle_name' => empty($data['middle_name']) ? null : $data['middle_name'],
                ':last_name' => $data['last_name'],
                ':specialization_id' => empty($data['specialization_id']) ? null : $data['specialization_id'],
                ':resident_district_id' => $data['resident_district_id'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function updateUser($data, $user_id, $updated_by)
    {
        if (array_key_exists('gender', $data)) {
            $data['sex'] = $data['gender'];
        }
        $sql = "UPDATE users SET
          first_name=:first_name, middle_name=:middle_name,
          last_name=:last_name, full_name=:full_name,
          phone_numbers=:phone_numbers, email=:email,
          staff_code=:staff_code, sex=:sex, marital_status=:marital_status,
          dob=:dob,rssb_number=:rssb_number, nationality_id=:nationality_id,
          bank_account=:bank_account, bank_id=:bank_id,
          specialization_id=:specialization_id,village_code=:village_code,
          education_domain_id=:education_domain_id,education_sub_dommain_id=:education_sub_dommain_id,graduation_date=:graduation_date,hired_date=:hired_date,nid=:nid,username=:username,resident_district_id=:resident_district_id,
          contract_type=:contract_type,updated_by=:updated_by,updated_at=:updated_at
          WHERE user_id = :user_id AND status =:status;
      ";
        try {

            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':staff_code' => $data['staff_code'],
                ':bank_id' => empty($data['bank_id']) ? null : $data['bank_id'],
                ':village_code' => empty($data['village_code']) ? null : $data['village_code'],
                ':full_name' => $data['full_name'],
                ':sex' => $data['sex'],
                ':dob' => empty($data['dob']) ? null : $data['dob'],
                ':marital_status' => empty($data['marital_status']) ? null : $data['marital_status'],
                ':nid' => $data['nid'],
                ':email' => $data['email'],
                ':phone_numbers' => $data['phone_numbers'],
                ':rssb_number' => empty($data['rssb_number']) ? null : $data['rssb_number'],
                ':education_domain_id' => empty($data['education_domain_id']) ? null : $data['education_domain_id'],
                ':education_sub_dommain_id' => empty($data['education_sub_dommain_id']) ? null : $data['education_sub_dommain_id'],
                ':graduation_date' => empty($data['graduation_date']) ? null : $data['graduation_date'],
                ':hired_date' => empty($data['hired_date']) ? null : $data['hired_date'],
                ':contract_type' => empty($data['contract_type']) ? null : $data['contract_type'],
                ':bank_account' => empty($data['bank_account']) ? null : $data['bank_account'],
                ':nationality_id' => empty($data['nationality_id']) ? 1 : $data['nationality_id'],
                ':username' => $data['phone_numbers'],
                ':first_name' => $data['first_name'],
                ':middle_name' => empty($data['middle_name']) ? null : $data['middle_name'],
                ':last_name' => $data['last_name'],
                ':specialization_id' => empty($data['specialization_id']) ? null : $data['specialization_id'],
                ':resident_district_id' => $data['resident_district_id'],
                ':user_id' => $user_id,
                ':updated_by' => $updated_by,
                ':updated_at' => date("Y-m-d H:i:s"),
                ':status' => 1,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function changePassword($data)
    {
        $sql = "UPDATE `users` SET `password`=:password,`updated_by`=:updated_by,`updated_at`=:updated_at WHERE `user_id`=:user_id AND `status`=:status";
        try {

            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':password' => $data['password'],
                ':user_id' => $data['user_id'],
                ':updated_by' => $data['updated_by'],
                ':updated_at' => date("Y-m-d H:i:s"),
                ':status' => 1,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function changeUsernameAndPassword($data, $user_id, $updated_by)
    {
        $sql = "
          UPDATE
              users
          SET
          username=:username,password=:password,updated_by=:updated_by,updated_at=:updated_at
          WHERE user_id=:user_id AND status=:status;
      ";
        try {

            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':username' => $data['username'],
                ':password' => $data['password'],
                ':user_id' => $user_id,
                ':updated_by' => $updated_by,
                ':updated_at' => date("Y-m-d H:i:s"),
                ':status' => 1,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findUsersByRole($role_id, $status, $page = 1)
    {
        $results_per_page = 10;
        $page_first_result = ($page - 1) * $results_per_page;
        $queryCount = "SELECT u.nid FROM users u
        INNER JOIN user_to_role ur ON u.user_id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.role_id
        WHERE ur.role_id = :role_id AND ur.status = :status AND u.status = 1";
        try {
            $resultCount = $this->db->prepare($queryCount);
            $resultCount->execute(array(":role_id" => $role_id, ":status" => $status));
            $number_of_result = $resultCount->rowCount();

            // determining the total number of pages available
            $number_of_page = ceil($number_of_result / $results_per_page);

            // Selecting from limited data from table
            $query = "SELECT u.staff_code, u.staff_category_id, u.full_name,u.sex,u.dob,u.marital_status,u.nid,u.email,u.phone_numbers,u.rssb_number,u.hired_date,u.contract_type,u.bank_account,u.nationality_id,u.province_code,u.district_code,u.sector_code,u.cell_code,u.village_id,u.village,u.school_code,u.school_name,u.user_id,u.disability,u.photo_url,u.first_name,u.middle_name,u.last_name,u.specialization_id,u.village_code, r.role_id, r.role, ur.status FROM users u
            INNER JOIN user_to_role ur ON u.user_id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.role_id = :role_id AND u.status = 1 AND ur.status = :status
            LIMIT " . $page_first_result . ',' . $results_per_page;
            $statement = $this->db->prepare($query);
            $statement->execute(array(":role_id" => $role_id, ":status" => $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            $object = new \stdClass();
            $object->total_pages = $number_of_page;
            $object->current_page = $page;
            $object->users = $result;
            return $object;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findUserByPhoneNumber($username)
    {
        $statement = "SELECT * FROM users WHERE phone_numbers=? AND status = ? LIMIT 1 ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($username, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findExistPhoneNumberEmailNid($phone_number, $email, $nid, $staff_code)
    {
        $statement = "SELECT U.*,
        R.role_id, R.role, UR.position_code, P.position_name,
        Q.qualification_id, Q.qualification_name, SCL.school_code, SCL.school_name, SCL.school_category, SCL.school_status FROM users U
        INNER JOIN user_to_role UR ON U.user_id = UR.user_id
        INNER JOIN roles R ON UR.role_id = R.role_id
        LEFT JOIN schools SCL ON UR.school_code = SCL.school_code
        LEFT JOIN positions P ON UR.position_code = P.position_code
        LEFT JOIN qualifications Q ON UR.qualification_id = Q.qualification_id
        WHERE UR.status = 'Active' AND U.phone_numbers=? OR U.email = ? OR U.nid = ? OR U.staff_code = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($phone_number, $email, $nid, $staff_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findExistPhoneNumberShort($phone_number)
    {
        $statement = "SELECT `user_id`, `full_name` FROM users WHERE phone_numbers=? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($phone_number));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * getting pages number of users
     * @param int $no_of_records_per_page
     * @return float $total_pages
     */
    public function getUsersPage($no_of_records_per_page)
    {
        $statement = "SELECT COUNT(*) FROM users";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array());
            $total_rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $total_pages = ceil($total_rows[0] / $no_of_records_per_page);
            return $total_pages;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findExistEmailShort($email)
    {
        $statement = "SELECT `user_id`, `full_name` FROM users WHERE email=? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($email));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findExistNidShort($nid)
    {
        $statement = "SELECT `user_id`, `full_name` FROM users WHERE nid=? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($nid));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findExistStaffCodeShort($staffCode)
    {
        $statement = "SELECT `user_id`, `staff_code`, `full_name` FROM users WHERE staff_code=? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($staffCode));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findByUsername($username)
    {
        $statement = "SELECT * FROM users WHERE username = ? AND status = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($username, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findByUsernamePhoneNumberAndStaffCode($username)
    {
        $statement = "SELECT * FROM `users` WHERE `username` = :username OR `phone_numbers` = :phone_numbers OR `staff_code` = :staff_code  AND `status` = :status LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ":username" => $username,
                ":phone_numbers" => $username,
                ":staff_code" => $username,
                ":status" => 1,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findByUsernameShort($username)
    {
        $statement = "SELECT `user_id` FROM users WHERE username = ? AND status = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($username, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findOneUser($user_id, $phone_number = "", $status = 1)
    {
        $statement = "SELECT * FROM users WHERE user_id = ? OR staff_code = ?  AND status = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id, $user_id, $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findUserByUserId($user_id)
    {
        $statement = "SELECT * FROM users WHERE user_id = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findUserByStaffcode($staff_code)
    {
        $statement = "SELECT * FROM users WHERE staff_code = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($staff_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findOneUserShort($user_id, $phone_number = "", $status = 1)
    {
        $statement = "SELECT `user_id` FROM users WHERE user_id = ? OR staff_code = ? OR phone_numbers = ?  AND status = ? LIMIT 1";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id, $user_id, $phone_number, $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function changeStatus($user_id, $updated_by, $status)
    {
        $sql = "
            UPDATE
                users
            SET
                status=:status,updated_by=:updated_by,updated_at=:updated_at
            WHERE user_id = :user_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':updated_by' => $updated_by,
                ':updated_at' => date("Y-m-d H:i:s"),
                ':status' => $status,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
