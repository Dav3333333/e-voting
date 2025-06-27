<?php
NAMESPACE Dls\Evoting\models;

require_once(__DIR__ . '/../../vendor/autoload.php');

use DateTime;
use JsonSerializable;

/**
 * this is class is the representation of the object pf differente elections
 * like : presidence, secretarie,...
 * 
 * 
 * 
 */
class Poll implements JsonSerializable
{
    /**
     * the unique id of the scruttin or poll
     * @var int
     */
    private int $id;

    /**
     * the title of the poll
     * @var string
     */
    private String $title; 

    /**
     * the start date of poll
     * @var DateTime
     */
    private DateTime $date_start;

    /**
     * the end date of the poll
     * @var DateTime
     */
    private DateTime $date_end;

    /**
     * the statue of the poll, this can be actif, inactif or passed
     * @var string
     */
    private string $status; 

    /**
     * the descrition of the poll
     * @var string
     */
    private string $description;

    /**
     * the post of the poll
     * @var array
     */
    private array $posts_data;
    
    public function __construct(int $id, String $title, DateTime $date_start, DateTime $date_end, string $status, string $description){
        if ($date_end->getTimestamp() >= $date_start->getTimestamp()) {
            $this->id = $id;
            $this->title = $title;
            $this->date_start = $date_start;
            $this->date_end = $date_end;
            $this->status = $status;
            $this->description = $description;
            $this->posts_data = [];
        }else{
            return ;
        }
    }
    
    // loading functions

    public function getId(): int { return $this->id; }

    public function getTitle(): String { return $this->title; }

    public function getDateStart(): DateTime {
        return $this->date_start;
    }

    public function getDateEnd(): DateTime {
        return $this->date_end;
    }

    public function getStatus(): string { return $this->status; }

    public function getDesription(): string { return $this->description;}

    public function get_posts(): array { return $this->posts_data; }
    
    public function setTitre(?string $titre): void{
        $this->title = $titre;
    }

    public function setPosts(array $posts): void{
        $this->posts_data = $posts;
    }

    public function equals(Poll $poll){
        return $this->getId() === $poll->getId();
    }

    public function __toString(): string { return $this->title; }

    public function jsonSerialize(): array {
        return [
            "id"=> $this->id,
            "title"=> $this->title,
            "description"=> $this->description,
            "dateStart"=> $this->date_start,
            "dateEnd" => $this->date_end,
            "status"=> $this->status,
            "posts"=> $this->get_posts(),
        ];
    }

}
