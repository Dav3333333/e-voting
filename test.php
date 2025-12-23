<?php 

namespace Load;

require_once('config/Database.php'); 

$bd = new Database::getInstance();

var_dump($bd);
