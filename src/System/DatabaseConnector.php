<?php
namespace Src\System;

if(!class_exists('DatabaseConnector')):

    class DatabaseConnector {
        
    private  $server = "mysql:host=localhost;dbname=tmis";
    private  $user = "root";
    private  $password = "root";
    private  $options  = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,);
    private $dbConnection = null;

    public function __construct() {
        try {
            $this->dbConnection = new \PDO($this->server, $this->user,$this->password,$this->options);
        }
        catch (\PDOException $e) {
            exit($e->getMessage());
        }

    }

    public function getConnection(){
        return $this->dbConnection;
    }

    public function closeConnection() {
        return $this->connect = null;
    }

    }
endif;
?>