<?php
namespace Src\Models;

class UsersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function insert($data)
    {
        $statement = "
        INSERT
          INTO users
            (user_id,first_name,middle_name,last_name,full_name,phone_numbers,email,username,password,staff_code,sex,marital_status,nid,highest_qualification_id,dob,rssb_number,nationality_id,bank_account,bank_id,specialization_id,village_code,education_domain_id,education_sub_dommain_id,graduation_date,hired_date,contract_type,staff_category_id,created_by)
          VALUES (:user_id,:first_name,:middle_name,:last_name,:full_name,:phone_numbers,:email,:username,:password,:staff_code,:sex,:marital_status,:nid,:highest_qualification_id,:dob,:rssb_number,:nationality_id,:bank_account,:bank_id,:specialization_id,:village_code,:education_domain_id,:education_sub_dommain_id,:graduation_date,:hired_date,:contract_type,:staff_category_id,:created_by);
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':user_id' => $data['user_id'],
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'],
                ':last_name' => $data['last_name'],
                ':full_name' => $data['full_name'],
                ':phone_numbers' => $data['phone_numbers'],
                ':email' => $data['email'],
                ':username' => $data['username'],
                ':password' => $data['password'],
                ':staff_code' => $data['staff_code'],
                ':sex' => $data['sex'],
                ':marital_status' => $data['marital_status'],
                ':highest_qualification_id' => $data['highest_qualification_id'],
                ':dob' => $data['dob'],
                ':rssb_number' => $data['rssb_number'],
                ':nationality_id' => $data['nationality_id'],
                ':bank_account' => $data['bank_account'],
                ':bank_id' => $data['bank_id'],
                ':nid' => $data['nid'],
                ':specialization_id' => $data['specialization_id'],
                ':village_code' => $data['village_code'],
                ':education_domain_id' => $data['education_domain_id'],
                ':education_sub_dommain_id' => $data['education_sub_dommain_id'],
                ':graduation_date' => $data['graduation_date'],
                ':hired_date' => $data['hired_date'],
                ':contract_type' => $data['contract_type'],
                ':staff_category_id' => $data['staff_category_id'],
                ':created_by' => $data['created_by'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function updateUser($data, $user_id, $updated_by)
    {
        $sql = "
          UPDATE
              users
          SET
          first_name=:first_name,middle_name=:middle_name,
          last_name=:last_name,full_name=:full_name,
          phone_numbers=:phone_numbers,email=:email,
          staff_code=:staff_code,sex=:sex,marital_status=:marital_status,
          dob=:dob,rssb_number=:rssb_number,nationality_id=:nationality_id,
          bank_account=:bank_account,bank_id=:bank_id,
          specialization_id=:specialization_id,village_code=:village_code,
          education_domain_id=:education_domain_id,education_sub_dommain_id=:education_sub_dommain_id,graduation_date=:graduation_date,hired_date=:hired_date,
          contract_type=:contract_type,updated_by=:updated_by,updated_at=:updated_at
          WHERE user_id = :user_id AND status =:status;
      ";
        try {

            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'],
                ':last_name' => $data['last_name'],
                ':full_name' => $data['full_name'],
                ':phone_numbers' => $data['phone_numbers'],
                ':email' => $data['email'],
                ':staff_code' => $data['staff_code'],
                ':sex' => $data['sex'],
                ':marital_status' => $data['marital_status'],
                ':dob' => $data['dob'],
                ':rssb_number' => $data['rssb_number'],
                ':nationality_id' => $data['nationality_id'],
                ':bank_account' => $data['bank_account'],
                ':bank_id' => $data['bank_id'],
                ':specialization_id' => $data['specialization_id'],
                ':village_code' => $data['village_code'],
                ':education_domain_id' => $data['education_domain_id'],
                ':education_sub_dommain_id' => $data['education_sub_dommain_id'],
                ':graduation_date' => $data['graduation_date'],
                ':hired_date' => $data['hired_date'],
                ':contract_type' => $data['contract_type'],
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
    public function changePassword($data)
    {
        $sql = "
          UPDATE
              users
          SET
          password=:password,updated_by=:updated_by,updated_at=:updated_at
          WHERE user_id=:user_id AND status=:status;
      ";
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
    public function findUsersByRole($role_id, $page = 1)
    {
        $results_per_page = 10;
        $page_first_result = ($page - 1) * $results_per_page;
        $queryCount = "SELECT u.nid FROM users u
        INNER JOIN user_to_role ur ON u.user_id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.role_id
        WHERE ur.role_id = :role_id AND u.status = 1";
        try {
            $resultCount = $this->db->prepare($queryCount);
            $resultCount->execute(array(":role_id" => $role_id));
            $number_of_result = $resultCount->rowCount();

            // determining the total number of pages available
            $number_of_page = ceil($number_of_result / $results_per_page);

            // Selecting from limited data from table
            $query = "SELECT u.staff_code, u.staff_category_id, u.full_name,u.sex,u.dob,u.marital_status,u.nid,u.email,u.phone_numbers,u.rssb_number,u.hired_date,u.contract_type,u.bank_account,u.nationality_id,u.province_code,u.district_code,u.sector_code,u.cell_code,u.village_id,u.village,u.school_code,u.school_name,u.user_id,u.disability,u.photo_url,u.first_name,u.middle_name,u.last_name,u.specialization_id,u.village_code, r.role_id, r.role FROM users u
            INNER JOIN user_to_role ur ON u.user_id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.role_id = :role_id AND u.status = 1
            LIMIT " . $page_first_result . ',' . $results_per_page;
            $statement = $this->db->prepare($query);
            $statement->execute(array(":role_id" => $role_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            $object = new \stdClass();
            $object->total_pages = $number_of_page;
            $object->current_page = $page;
            $object->users = $result;
            return $object;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findById($user_id, $status)
    {
        $statement = "SELECT * FROM users WHERE user_id = ? OR staff_code = ?  AND status = ? LIMIT 1
      ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id, $user_id, $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
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

    public function findExistUserName($username, $user_id, $status)
    {
        $statement = "SELECT * FROM users WHERE username=? AND user_id != ? AND status = ? LIMIT 1
      ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($username, $user_id, $status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findByUsername($username)
    {
        $statement = "
          SELECT
              *
          FROM
              users WHERE username = ? AND status = ? LIMIT 1
      ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($username, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findOne($user_id)
    {
        $statement = "SELECT * FROM users WHERE user_id = ? OR staff_code = ?  AND status = ? LIMIT 1
      ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id, $user_id, 1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (sizeof($result) == 0) {
                return null;
            }
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
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
