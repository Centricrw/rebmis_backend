<?php
namespace Src\Models;

class SchoolLocationsModel {

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
        $statement->execute(array(1));
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

  public function findSectorByCoder($sector_code){
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
      $statement->execute(array($district_code.'%'));
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
        sector_name,sector_code 
      FROM 
        school_location 
      WHERE 
        sector_code LIKE ?
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($district_code.'%'));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>