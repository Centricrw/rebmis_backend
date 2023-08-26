<?php
namespace Src\Models;

class UserRoleModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function insert($data, $user_id)
    {
        $statement = "
        INSERT
          INTO user_to_role
            (role_to_user_id, role_id,qualification_id, start_date_in_the_school, school_code, user_id, country_id, district_code, sector_code, position_code, academic_year_id, stakeholder_id, created_by)
          VALUES
            (:role_to_user_id, :role_id, :qualification_id, :start_date_in_the_school, :school_code, :user_id, :country_id, :district_code, :sector_code, :position_code, :academic_year_id, :stakeholder_id, :created_by)
      ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':role_to_user_id' => $data['role_to_user_id'],
                ':user_id' => $data['user_id'],
                ':role_id' => $data['role_id'],
                ':country_id' => $data['country_id'],
                ':district_code' => $data['district_code'],
                ':sector_code' => $data['sector_code'],
                ':position_code' => $data['position_code'],
                ':school_code' => $data['school_code'],
                ':qualification_id' => $data['qualification_id'],
                ':academic_year_id' => $data['academic_year_id'],
                ':start_date_in_the_school' => date("Y-m-d H:i:s"),
                ':stakeholder_id' => $data['stakeholder_id'],
                ':created_by' => $user_id,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
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
                ':user_id' => $data['user_id'],
                ':role_id' => $data['role_id'],
                ':country_id' => $data['country_id'],
                ':district_code' => $data['district_code'],
                ':sector_code' => $data['sector_code'],
                ':school_code' => $data['school_code'],
                ':qualification_id' => $data['qualification_id'],
                ':academic_year_id' => $data['academic_year_id'],
                ':stakeholder_id' => $data['stakeholder_id'],
                ':updated_by' => $user_id,
                ':status' => 1,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function saveRetrieved($data)
    {
        $statement = "
        INSERT INTO user_to_role
          (role_to_user_id,user_id,role_id,school_code,position_code,academic_year_id,updated_by,status)
        VALUES
          (:role_to_user_id,:user_id,:role_id,:school_code,:position_code,:academic_year_id,:updated_by,:status)
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':role_to_user_id' => $data->user_id,
                ':user_id' => $data->user_id,
                ':role_id' => $data->role_id,
                ':school_code' => $data->school_code,
                ':position_code' => $data->position_code,
                ':academic_year_id' => $data->academic_year_id,
                ':updated_by' => $data->user_id,
                ':status' => "New",
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function assignPosition($data)
    {
        $sql = "
            UPDATE
              user_to_role
            SET
              position_code=:position_code
            WHERE
              user_id=:user_id AND school_code=:school_code
        ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $data['user_id'],
                ':school_code' => $data['school_code'],
                ':position_code' => $data['position_code'],
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findUserPosition($data)
    {
        $sql = "
        SELECT
          *
        FROM
          user_to_role
        WHERE
          user_id=:user_id AND school_code=:school_code";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $data['user_id'],
                ':school_code' => $data['school_code'],
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findCurrentUserRole($user_id)
    {
        $sql = "
        SELECT ur.*, r.role FROM
            user_to_role ur, roles r
        WHERE
          ur.user_id=:user_id AND ur.status=:status AND ur.role_id=r.role_id";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':user_id' => $user_id,
                ':status' => "Active",
            ));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findByUserID($user_id)
    {
        $sql = "
        SELECT
          *
        FROM
          user_to_role
        WHERE user_id=?
        ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($user_id));

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findCurrentSchoolTeachers($school_code, $academic_year_id)
    {
        $sql = "
            SELECT
                user_id,role_id,position_code,qualification_id,academic_year_id,status
            FROM
                user_to_role
            WHERE school_code=:school_code AND academic_year_id=:academic_year_id AND status=:status
        ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':academic_year_id' => $academic_year_id,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findSchoolTeachers($school_code)
    {
        $sql = "
          SELECT
              user_id,role_id,position_code,qualification_id,academic_year_id,status
          FROM
              user_to_role
          WHERE school_code=:school_code AND status=:status
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findSchoolDisabledTeachers($district_code)
    {
        $sql = "
          SELECT
              user_id,role_id,qualification_id,academic_year_id,status
          FROM
              user_to_role
          WHERE school_code like ? AND status=?
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($district_code . '%', "Disabled"));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findSchoolDisabledStaffs($status)
    {
        $sql = "
      SELECT
        DISTINCT ur.user_id, u.*,ur.*
      FROM
        users u, user_to_role ur
      WHERE
        u.user_id=ur.user_id AND ur.status=? ORDER BY ur.updated_at LIMIT 10;
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($status));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findUserByNid($nid)
    {
        $sql = "
      SELECT
        DISTINCT ur.user_id, u.*,ur.*
      FROM
        users u, user_to_role ur
      WHERE
        u.user_id=ur.user_id AND u.nid=? ORDER BY ur.updated_at;
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($nid));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findTotalSchoolTeachers($school_code)
    {
        $sql = "
          SELECT
              COUNT(*) as total_teachers
          FROM
              user_to_role
          WHERE school_code=:school_code AND role_id!=:role_id AND status=:status
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':role_id' => 2,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findTotalTeacherByQualifications($academic_year_id)
    {
        $sql = "
        SELECT
          qualification_id, COUNT(*) as total_teachers
        FROM
            user_to_role
        WHERE
          academic_year_id=:academic_year_id AND status=:status GROUP BY qualification_id
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':academic_year_id' => $academic_year_id,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findTotalSchoolTeacherByStatus($school_code)
    {
        $sql = "
          SELECT
              COUNT(*) as total_teachers
          FROM
              user_to_role
          WHERE school_code=:school_code AND role_id!=:role_id AND status=:status
      ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':role_id' => 2,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function occupiedPosition($school_code)
    {
        $sql = "
      SELECT
          ur.qualification_id, ur.position_code,COUNT(ur.position_code) AS occupied_number
      FROM
          user_to_role ur, school_has_positions shp
      WHERE
          ur.school_code=:school_code AND ur.position_code=shp.position_code  AND ur.school_code=shp.school_code AND ur.status=:status GROUP BY ur.position_code
    ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function schoolOccupiedPosition($school_code, $position_code, $qualification_id, $academic_year_id)
    {
        $sql = "
    SELECT
     COUNT(ur.position_code) as occupied
    FROM
      user_to_role ur,school_has_positions shp
    WHERE
      ur.school_code=:school_code AND ur.position_code=:position_code and ur.position_code=shp.position_code AND ur.school_code=shp.school_code AND ur.qualification_id=:qualification_id AND ur.academic_year_id=:academic_year_id AND ur.status=:status
    ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':school_code' => $school_code,
                ':position_code' => $position_code,
                ':qualification_id' => $qualification_id,
                ':academic_year_id' => $academic_year_id,
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function schoolOccupiedPositions($district_code)
    {
        $sql = "
    SELECT
      COUNT(ur.position_code) as occupied,ur.school_code,ur.position_code,ur.qualification_id
    FROM
      user_to_role ur
    WHERE
      SUBSTRING(ur.school_code,0,1)=$district_code AND position_code IS NOT NULL AND position_code <> '' AND ur.status=:status GROUP BY ur.position_code, ur.school_code;
    ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                ':status' => "Active",
            ));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function districtSchoolOccupation($district_code, $academic_year_id)
    {
        $sql = "
    SELECT
      ur.school_code, ur.qualification_id, ur.position_code, COUNT(ur.position_code) as occupied
    FROM user_to_role ur WHERE ur.district_code=? AND ur.academic_year_id=? AND ur.position_code <> '' AND ur.school_code <> '' GROUP BY ur.district_code, ur.position_code, ur.qualification_id
    ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($district_code, $academic_year_id));
            $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $statement;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function disableRole($user_id, $updated_by, $target, $status)
    {
        $sql = "
          UPDATE
            user_to_role
          SET
            status=:status,updated_by=:updated_by,updated_at=:updated_at
          WHERE
            user_id=:user_id AND status=:target;
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
    public function savePermutedUser($data)
    {
        $statement = "
      INSERT
        INTO user_to_role
          (role_to_user_id, role_id,qualification_id, start_date_in_the_school, school_code, user_id, position_code, academic_year_id, created_by)
        VALUES
          (:role_to_user_one, :role_id, :qualification_id, :start_date_in_the_school, :school_two, :user_one, :position_code, :academic_year_id, :created_by),
          (:role_to_user_two, :role_id, :qualification_id, :start_date_in_the_school, :school_one, :user_two, :position_code, :academic_year_id, :created_by)
    ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':role_to_user_one' => $data['role_to_user_one'],
                ':role_to_user_two' => $data['role_to_user_two'],
                ':user_one' => $data['user_one'],
                ':user_two' => $data['user_two'],
                ':role_id' => $data['role_id'],
                ':position_code' => $data['position_code'],
                ':school_one' => $data['school_one'],
                ':school_two' => $data['school_two'],
                ':qualification_id' => $data['qualification_id'],
                ':academic_year_id' => $data['academic_year_id'],
                ':start_date_in_the_school' => date("Y-m-d H:i:s"),
                ':created_by' => $data['user_one'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function totalTeacherOnQualification($district_code)
    {
        $sql = "
      SELECT
        q.qualification_name, COUNT(ur.qualification_id) AS total
      FROM
        user_to_role ur, qualifications q
      WHERE
        ur.status='Active' AND ur.school_code LIKE CONCAT('$district_code','%') AND ur.qualification_id=q.qualification_id GROUP by ur.qualification_id
    ";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($district_code));
            $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $statement;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
