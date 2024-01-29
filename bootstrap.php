<?php
session_start();
require_once 'vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

// $dbConnection = (new DatabaseConnector())->getConnection();
// $token = (new Token());
