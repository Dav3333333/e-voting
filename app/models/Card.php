<?php
NAMESPACE Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JsonSerializable;

/**
 * this is the onject the represent a acceesto a vote
 */
class Card implements JsonSerializable
{
    private int $id; 

    private String $user_matricule;

    private String $code_card;

    public function __construct(int $id, String $user_matricule, String $code_card) {
        $this->id = $id; 
        $this->user_matricule = $user_matricule;
        $this->code_card = $code_card;
    }

    public function get_user_matricule():String{
        return $this->user_matricule;
    }

    public function set_user_matricule(String $new_matricule):void{
        $this->user_matricule = $new_matricule;
    }

    public function get_code_card():String{
        return $this->code_card;
    }

    public function set_code_card(String $new_code_card):void{
        $this->code_card = $new_code_card;
    }

    public function jsonSerialize():array{
        return [
            "id"=> $this->id,
            "userId"=> $this->user_matricule,
            "codeCard"=> $this->code_card
        ];
    }
}

