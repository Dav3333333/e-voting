<?php 

NAMESPACE Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');
// require_once("./config/Database.php");
require_once(__DIR__."/../../config/Database.php");

use Config\Database;
use PDO;
Use DateTime;


class ControllersParent {

    protected PDO $database;

    protected DateTime $dateTime; 


    public function __construct() {  
        $this->database = Database::getInstance();
        $this->dateTime = new DateTime();
    }
}



