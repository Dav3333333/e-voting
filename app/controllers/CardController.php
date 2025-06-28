<?php 
namespace Dls\Evoting\controllers; 

require_once(__DIR__ . '/../../vendor/autoload.php');


use Dls\Evoting\controllers\ControllersParent; 


class CardController extends ControllersParent
{

    public function getAll():array{
        $cards = [];

        return $cards;
    }
    
}
