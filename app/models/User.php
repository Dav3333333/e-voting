<?php
NAMESPACE Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JsonSerializable;

/**
 * this object represent a user on the system
 */
class User implements JsonSerializable
{

    private int $id;

    private String $name;
    
    private String $email; 

    private String $matricule;

    private String $RFID;

    private String $status; 

    private bool $isAdmin;

    public function __construct(int $id, String $name, String $email, String $matricule, String $RFID,bool $isAdmin, String $status) {
        $this->id = $id;
        $this->name = $name; 
        $this->email = $email;
        $this->matricule = $matricule;
        $this->RFID = $RFID;
        $this->isAdmin = $isAdmin;
        $this->status = $status;
    }

    public function getId():int{
        return $this->id;
    }

    public function getName():String{
        return $this->name;
    }

    public function setName(String $name):void{
        $this->name = $name;
    }

    public function getEmail():String{
        return $this->email;
    }

    public function getMatricule():String{
        return $this->matricule;
    }

    public function setMatricule(String $matricule):void{
        $this->$matricule;
    }

    public function getStatus():String{
        return $this->status;
    }

    public function setStatus(String $status):void{
        $this->$status;
    }

    public function isAdmin():bool{
        return $this->isAdmin;
    }

    public function jsonSerialize():array{
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "email"=> $this->email,
            "matricule"=> $this->matricule, 
            "code_rfid"=> $this->RFID,
            "status"=> $this->status
        ];
    }

}
