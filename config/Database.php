<?php
NAMESPACE Config;

use PDO;


class Database
{
    private static $instance = null;

    public static function getInstance(){

        $host = "localhost";
    
        $db_name = "evoting";

        $db_user = "root"; 

        $db_password = "";

        if(self::$instance == null){
            self::$instance = new PDO("mysql:host={$host};dbname={$db_name}","{$db_user}","{$db_password}",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        return self::$instance;
    }
    
}

// $hostname = "localhost";
// $dbname = "ucbc_gaff";
// $username = "root";
// $password = "";

// $bdd = new PDO("mysql:host={$hostname};dbname={$dbname}","{$username}","{$password}",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

