<?php 
namespace Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');


use JsonSerializable;

/**
 * this object reprensent a candidate to an election
 */
class Candidate implements JsonSerializable{
    
    private int $id;

    private string $user_matricule;

    private int $post_id;

    private bool $status;

    public function __construct(int $id, string $user_matricule, int $post_id, bool $status){
        $this->id = $id;
        $this->user_matricule = $user_matricule;
        $this->post_id = $post_id;
        $this->status = $status;
    }

    public function getId(): int{ return $this->id;}

    public function getUserMatricule(): string{return $this->user_matricule;}

    public function getPostId(): int{return $this->post_id;}

    public function getStatus(): bool{return $this->status;}

    public function __toString():string{return `$this->id`;}

    public function jsonSerialize():array{
        return [
            "id"=> $this->id,
            "userId"=> $this->user_matricule,
            "postId"=> $this->post_id,
            "status"=> $this->status, 
        ];
    }
}