<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

/*
$host = "mealcheccjmealch.mysql.db";
$user = "mealcheccjmealch";
$password = "Laisset85";
$dbname = "mealcheccjmealch";
*/

$host = "localhost";
$user = "root";
$password = "";
$dbname = "newmealcheck";

$pdo = null;
try{
$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname,
        $user,
        $password,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e) {
  echo "Erreur!: " . $e->getMessage() . "<br/>";
  die();
}

