<?php
namespace Src\Models;

use Error;

class UserRoleModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function insertIntoUserToRole($data, $user_id)
    {
        $statement = "INSERT INTO user_to_role (role_to_user_id, role_id,qualification_id, position_code, start_date_in_the_school, school_code, user_id, country_id, district_code, sector_code, academic_year_id, stakeholder_id, created_by) VALUES (:role_to_user_id, :role_id, :qualification_id, :position_code, :start_date_in_the_school, :school_code, :user_id, :country_id, :district_code, :sector_code, :academic_year_id, :stakeholder_id, :created_by)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':role_to_user_id' => $data['role_to_user_id'],
                ':user_id' => $data['user_id'],
                ':role_id' => $data['role_id'],
                ':country_id' => empty($data['country_id']) ? 1 : $data['country_id'],
                ':district_code' => empty($data['district_code']) ? null : $data['district_code'],
                ':sector_code' => empty($data['sector_code']) ? null : $data['sector_code'],
                ':school_code' => empty($data['school_code']) ? null : $data['school_code'],
                ':qualification_id' => empty($data['qualification_id']) ? null : $data['qualification_id'],
                ':position_code' => empty($data['position_code']) ? null : $data['position_code'],
                ':academic_year_id' => empty($data['academic_year_id']) ? null : $data['academic_year_id'],
                ':start_date_in_the_school' => empty($data['start_date_in_the_school']) ? null : $data['start_date_in_the_school'],
                ':stakeholder_id' => empty($data['stakeholder_id']) ? null : $data['stakeholder_id'],
                ':created_by' => $user_id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function insertIntoUserToRoleCustom($data)
    {
        $statement = "INSERT INTO user_to_role_custom (`cohort_id`, `school_code`, `staff_code`, `custom_role`) VALUES (:cohort_id,:school_code, :staff_code, :custom_role)";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohort_id' => $data['cohort_id'],
                ':school_code' => $data['school_code'],
                ':staff_code' => $data['staff_code'],
                ':custom_role' => $data['custom_role'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectUserToRoleCustom($data)
    {
        $statement = "SELECT * FROM `user_to_role_custom` WHERE `cohort_id`=:cohort_id AND `school_code` = :school_code AND `staff_code` = :staff_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohort_id' => $data['cohort_id'],
                ':school_code' => $data['school_code'],
                ':staff_code' => $data['staff_code'],
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function selectUserToRoleCustomShort($data)
    {
        $statement = "SELECT `staff_code` FROM `user_to_role_custom` WHERE `cohort_id`=:cohort_id AND `school_code` = :school_code AND `staff_code` = :staff_code";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohort_id' => $data['cohort_id'],
                ':school_code' => $data['school_code'],
                ':staff_code' => $data['staff_code'],
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function update($data, $user_id)
    {
        $statement = "
        UPDATE
           user_to_role
        SET
            role_id=:role_id,country_id=:country_id,district_code=:district_code,sector_code=:sector_code,school_code=:school_code,qualification_id=:qualification_id,updated_by=:updated_by
          WHERE user_id=:user_id AND status=:status);
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':country_id' => empty($data['country_id']) ? 1 : $data['country_id'],
                ':district_code' => empty($data['district_code']) ? null : $data['district_code'],
                ':sector_code' => empty($data['sector_code']) ? null : $data['sector_code'],
                ':school_code' => empty($data['school_code']) ? null : $data['school_code'],
                ':qualification_id' => empty($data['qualification_id']) ? null : $data['qualification_id'],
                ':academic_year_id' => empty($data['academic_year_id']) ? null : $data['academic_year_id'],
                ':stakeholder_id' => empty($data['stakeholder_id']) ? null : $data['stakeholder_id'],
                ':user_id' => $data['user_id'],
                ':role_id' => $data['role_id'],
                ':updated_by' => $user_id,
                ':status' => "Active",
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findCurrentUserRole($user_id, $status = "Active")
    {
        $sql = "SELECT ur.*, r.role FROM user_to_role ur, roles r WHERE
          ur.user_id=:user_id AND ur.status=:status AND ur.role_id=r.role_id";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':status' => $status,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findCurrentUserRoleShort($user_id, $status = "Active")
    {
        $sql = "SELECT `user_id` FROM `user_to_role` WHERE `user_id`=:user_id AND `status`=:status";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':status' => $status,
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function findCurrentSchoolTeachers($school_code)
    {
        $sql = "
            SELECT
                user_id,role_id,qualification_id,academic_year_id
            FROM
                user_to_role
            WHERE school_code=:school_code
        ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function disableRole($user_id, $updated_by, $target, $status)
    {
        $sql = "UPDATE user_to_role SET `status`=:status, `updated_by`=:updated_by, `updated_at`=:updated_at
          WHERE `user_id`=:user_id AND `status`=:target;
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':updated_by' => $updated_by,
                ':updated_at' => date("Y-m-d H:i:s"),
                ':target' => $target,
                ':status' => $status,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function updateUserToRoleStatus($user_id, $updated_by, $currentStatus, $NewStatus)
    {
        $sql = "UPDATE user_to_role SET `status`=:status, `updated_by`=:updated_by, `updated_at`=:updated_at
          WHERE `user_id`=:user_id AND `status`=:target;
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':updated_by' => $updated_by,
                ':updated_at' => date("Y-m-d H:i:s"),
                ':target' => $currentStatus,
                ':status' => $NewStatus,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
