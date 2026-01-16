<?php
namespace Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use DateTime;

use JsonSerializable;

/**
 * this class represent a choice of some one in a election
 */
class Voice implements JsonSerializable
{
    private int $id; 

    private int $PollId;

    private int $postId; 

    private int $userId;

    private int $candidateId;

    private DateTime $timestamp;

    public function __constructfinal (int $id, int $PollId, int $postId, int $userId, int $candidateId, DateTime $timestamp){
        $this->id = $id;
        $this->PollId = $PollId;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->candidateId = $candidateId;
        $this->timestamp = $timestamp;
    }

    public function getId(): int { return $this->id; }

    public function getPollId(): int { return $this->PollId;}

    public function getPostId(): int { return $this->postId;}

    public function getUserId(): int { return $this->userId;}

    public function getCandidateId(): int { return $this->candidateId;}

    public function getTimestamp(): DateTime { return $this->timestamp;}
    
    public function jsonSerialize(): array {
        return [
            "id"=> $this->id,
            "pollId"=> $this->PollId,
            "postId"=> $this->postId,
            "userId"=> $this->userId,
            "candidateId"=> $this->candidateId,
            "datetime"=> $this->timestamp
        ];
    }
}
