<?php 
namespace Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JsonSerializable;

/**
 * this object represent the result to an election, and this sould be written
 * in the data base at the end of a session of election
 */
class Result implements JsonSerializable{

    private int $id; 

    private int $pollId; 

    private int $postId; 

    private int $candidateId;

    private int $voices;

    public function __construct(int $id, int $pollId, int $postId, int $candidateId, int $voices){
        $this->id = $id;
        $this->pollId = $pollId;
        $this->postId = $postId;
        $this->candidateId = $candidateId;
        $this->voices = $voices;
    }
    public function getId(): int{ return $this->id;}

    public function getPollId(): int{ return $this->pollId;}

    public function getPostId(): int{ return $this->postId;}

    public function getCandidateId(): int{ return $this->candidateId;}

    public function getVotes(): int{ return $this->voices;}

    public function jsonSerialize(): array{
        return [
            "id"=> $this->id,
            "pollId"=> $this->pollId,
            "postId"=> $this->postId,
            "candidateId"=> $this->candidateId,
            "voices"=> $this->voices, 
        ];
    }
}