<?php
namespace Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JsonSerializable;

/**
 * 
 * this object represent a post or service where the need a leader to vote
 */
class Post implements JsonSerializable
{

    private int $id;

    private int $pollId;

    private string $post_name;

    private array $candidateList;

    public function __construct(int $id, int $pollId, string $post_name, array $candidateList){
        $this->id = $id;
        $this->pollId = $pollId;
        $this->post_name = $post_name;
        $this->candidateList = $candidateList;
    }

    public function getId(): int{return $this->id;}

    public function getPollId(): int{return $this->pollId;}

    public function getPostName(): string{return $this->post_name;}

    public function isEqual(Post $post):bool{
        return ($this->id == $post->id && $this->pollId == $post->pollId);
    }

    public function __toString():string{return $this->post_name;}

    public function jsonSerialize():array{
        return [
            "id"=> $this->id,
            "pollId"=> $this->pollId,
            "postName"=> $this->post_name, 
            "candidateList"=>$this->candidateList,
        ];
    }

}
