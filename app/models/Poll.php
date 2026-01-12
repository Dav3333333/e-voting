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

    /**
     * the number of days before the poll start
     * @var array
     */
    private array $dayLeft;
    
    /**
     * the cards of this poll, false if is not in cardMode, empty array if emptymode
     * @var array|bool
    */
    private array|bool $card_data;

    /**
     * true is the pull is in card mode, false if not
     * @var bool
     */
    private bool $in_card_mode;

    /**
     * true if for this poll the user 
     * @var bool
     */
    private bool $card_user_link_mode;
    
    public function __construct(int $id, String $title, DateTime $date_start, DateTime $date_end, string $status,string $description, bool $in_card_mode, bool $card_user_link_mode){
        $currenDate = new DateTime();
        if ($date_end->getTimestamp() >= $date_start->getTimestamp()) {
            $this->id = $id;
            $this->title = $title;
            $this->date_start = $date_start;
            $this->date_end = $date_end;
            $this->status = $status;
            $this->description = $description;
            $this->posts_data = [];
            $this->in_card_mode = $in_card_mode;
            $this->card_user_link_mode = $card_user_link_mode;

            if($this->in_card_mode){
                $this->card_data = [];
            }else{
                $this->card_data = false;
            }

            $diffDates = $currenDate->diff($date_start, absolute:true);

            $this->dayLeft = [
                                "months"=>$diffDates->m,
                                "days"=>$diffDates->days,
                                "hours"=>$diffDates->h,
                                "munites"=>$diffDates->i, 
                            ];
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

    public function getDayLeft():array{return $this->dayLeft;}

    public function getInCardMode():bool{return $this->in_card_mode;}

    public function getIsCard_user_link_mode():bool{ return $this->card_user_link_mode;}
    
    public function setTitre(?string $titre): void{
        $this->title = $titre;
    }

    public function setPosts(array $posts): void{
        $this->posts_data = $posts;
    }

    public function equals(Poll $poll){
        return $this->getId() === $poll->getId();
    }

    private function getCards():array|bool{
        return $this->card_data;
    }

    public function hasMode():bool{
        return $this->card_user_link_mode || $this->in_card_mode;
    }

    public function getMode():string{
        if($this->card_user_link_mode) return "user-link-cardmode";
        if($this->in_card_mode) return "cardmode"; 
        return "null";
    }
    
    // public function getPollModeString():string{
    //     if ($this->getInCardMode()) return 'card_mode';
    //     if ($this->getIsCard_user_link_mode()) return "card_user_link_mode"; 
    //     return null;
    // }

    public function __toString(): string { return $this->title; }

    public function jsonSerialize(): array {
        return [
            "id"=> $this->id,
            "title"=> $this->title,
            "description"=> $this->description,
            "dateStart"=> $this->date_start,
            "dateEnd" => $this->date_end,
            "status"=> $this->status,
            "dayBefore"=> $this->dayLeft,
            "posts"=> $this->get_posts(),
            // false if not in card mode, list objet of card if in card mode
            "cardData"=>$this->getCards(),
            "mode"=> $this->getMode()
        ];
    }

}
