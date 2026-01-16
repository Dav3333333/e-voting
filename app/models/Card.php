<?php
namespace Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JsonSerializable;

/**
 * this is the onject the represent a acceesto a vote
 */
class Card implements JsonSerializable
{
    private int $id; 

    private int $poll_id;

    private String $code_card;

    private bool $linkableToUser;

    private int|null $linkedUser;

    private bool $used; 

    public function __construct(int $id, int $poll_id, String $code_card, bool $used, bool $linkable, int $linkedUser) {
        $this->id = $id; 
        $this->poll_id = $poll_id;
        $this->code_card = $code_card;
        $this->used = $used;
        $this->linkableToUser = $linkable; 

        if(is_int($linkedUser) && $linkedUser > 0){
            $this->linkedUser = $linkedUser; 
        }else{
            $this->linkedUser = null;
        }
    }

    public function get_id():int{
        return $this->id;
    }

    public function get_code_card():String{
        return $this->code_card;
    }

    public function set_code_card(String $new_code_card):void{
        $this->code_card = $new_code_card;
    }

    public function get_poll_id():int{
        return $this->poll_id;
    }

    public function isUsed():bool{
        return $this->used;
    }

    public function isLinkable():bool{
        return $this->linkableToUser;
    }

    public function getLinkedUser():int|null{
        return $this->linkedUser;
    }

    public function getMode():string{
        if($this->linkableToUser && $this->linkedUser) return "user-link-cardmode";
        return "cardmode"; 
    }

    public function jsonSerialize():array{
        $data =  [
            "id"=> $this->id,
            "poll_id"=> $this->poll_id,
            "codeCard"=> $this->code_card,
            "used" => $this->used, 
        ];

        if($this->isLinkable()) $data[] = ['linkable'=>$this->linkableToUser, 'linkedUser'=>$this->linkedUser];

        return $data;
    }
}

