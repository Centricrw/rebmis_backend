<?php
namespace Src\Models;

class SchoolLocationsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function districts()
    {
        $statement = "
        SELECT DISTINCT
            district_code, district_name
        FROM school_location ORDER BY district_code, district_name
      ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findDistrictByCode($district_code)
    {
        $statement = "
      SELECT DISTINCT
        district_code, district_name
      FROM
        school_location
      WHERE
        district_code=? ORDER BY district_code, district_name
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($district_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findOneDistrictByCode($district_code)
    {
        $statement = "
      SELECT DISTINCT
        district_code, district_name
      FROM
        school_location
      WHERE
        district_code=? ORDER BY district_code, district_name LIMIT 1
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($district_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findSectorByCoder($sector_code)
    {
        $sql = "
      SELECT DISTINCT
        sector_code, sector_name
      FROM
        school_location
      WHERE
        sector_code=? ORDER BY sector_code, sector_name";
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($sector_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findDistrictSchools($district_code)
    {
        $statement = "
      SELECT
         *
        FROM
          schools
      WHERE
        region_code LIKE ?
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($district_code . '%'));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findDistrictSectors($district_code)
    {
        $statement = "
      SELECT
        DISTINCT sector_name,sector_code
      FROM
        school_location
      WHERE
        sector_code LIKE ?
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($district_code . '%'));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findRegionCode($village_id)
    {
        $statement = "
      SELECT
        DISTINCT village_id, village_name, cell_name, cell_code, sector_name,sector_code, district_name, district_code, province_name, province_code
      FROM
        school_location
      WHERE
    village_id=? ORDER BY village_id
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($village_id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findDistrictSchoolOverview($district)
    {
        $sql = "
    SELECT COUNT(ur.role_id) AS total,r.role, u.sex FROM user_to_role ur, users u, roles r
    WHERE ur.role_id=r.role_id AND ur.user_id=u.user_id AND u.sex <> '' AND ur.school_code LIKE ? '%' AND ur.status=? GROUP BY u.sex, r.role_id
    ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array($district, "Active"));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
