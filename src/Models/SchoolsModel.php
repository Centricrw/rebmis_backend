<?php
namespace Src\Models;

class SchoolsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function findAll()
    {
        $statement = "
          SELECT
              *
          FROM
            schools
      ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(1));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findByCode($school_code)
    {
        $statement = "
        SELECT
            *
        FROM
            schools WHERE school_code=?
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($school_code));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findByDistrictCode($district_code)
    {
        $statement = "
        SELECT
            *
        FROM
            schools WHERE region_code like ? LIMIT 1
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

    public function findSchoolInSet($school_one, $school_two)
    {
        $sql = "
      SELECT
        school_name,school_code
      FROM
        schools
      WHERE
        FIND_IN_SET(school_code,'$school_one,$school_two')
    ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (sizeof($result) == 0) {
                return null;
            }
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
