<?php
namespace Src\Models;
  
class LocationsModel {
  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function getprovince($provinceId)
  {
    $availableteachers = new \stdClass();
    $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
    LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
    WHERE SCF.province_code = ?";
    $statement2 = "SELECT * FROM districts WHERE provincecode = ?";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($provinceId));
      $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
      $statement2 = $this->db->prepare($statement2);
      $statement2->execute(array($provinceId));
      $districts = $statement2->fetchAll(\PDO::FETCH_ASSOC);
      $availableteachers->total= $teachers[0];
      $availableteachers->locations=$districts;
      return $availableteachers;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }

  public function getdistricts($provinceId)
  {
    $availableteachers = new \stdClass();
      $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
      LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
      WHERE SCF.district_code = ?";
      $statement2 = "SELECT * FROM sectors WHERE districtcode = ?";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($provinceId));
        $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement2 = $this->db->prepare($statement2);
        $statement2->execute(array($provinceId));
        $districts = $statement2->fetchAll(\PDO::FETCH_ASSOC);
        $availableteachers->total= $teachers[0];
        $availableteachers->locations=$districts;
        return $availableteachers;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
  }

  public function getsectors($sectorcode)
  {
    $availableteachers = new \stdClass();
    $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
    LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
    WHERE SCF.sector_code = ?";
    $statement2 = "SELECT * FROM cells WHERE sectorcode = ?";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($sectorcode));
      $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
      $statement2 = $this->db->prepare($statement2);
      $statement2->execute(array($sectorcode));
      $districts = $statement2->fetchAll(\PDO::FETCH_ASSOC);
      $availableteachers->total= $teachers[0];
      $availableteachers->locations=$districts;
      return $availableteachers;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }

  public function getcells($cellcode)
  {
    $availableteachers = new \stdClass();
    $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
    LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
    WHERE SCF.cell_code = ?";
    $statement2 = "SELECT * FROM vilages WHERE codecell = ?";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($cellcode));
      $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
      $statement2 = $this->db->prepare($statement2);
      $statement2->execute(array($cellcode));
      $districts = $statement2->fetchAll(\PDO::FETCH_ASSOC);
      $availableteachers->total= $teachers[0];
      $availableteachers->locations=$districts;
      return $availableteachers;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }

  public function getvillages($villagecode)
  {
    $availableteachers = new \stdClass();
    $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
    LEFT JOIN schools_conf SCF ON SCF.school_code = UR.school_code
    WHERE SCF.village_id = ?";
    $statement2 = "SELECT * FROM schools WHERE region_code = ?";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($villagecode));
      $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
      $statement2 = $this->db->prepare($statement2);
      $statement2->execute(array($villagecode));
      $districts = $statement2->fetchAll(\PDO::FETCH_ASSOC);
      $availableteachers->total= $teachers[0];
      $availableteachers->locations=$districts;
      return $availableteachers;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }

  public function getperschool($schoolcode)
  {
    $availableteachers = new \stdClass();
    $statement = "SELECT COUNT(UR.role_to_user_id) available_teachers FROM user_to_role UR 
    WHERE UR.school_code = ?";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($schoolcode));
      $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
      $availableteachers->total= $teachers[0];
      return $availableteachers;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
}
?>