<?php
namespace Dls\Evoting\controllers; 

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;

use Dls\Evoting\models\Candidate;
use Dls\Evoting\models\Card;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post; 
use Dls\Evoting\models\User;

use FPDF;


class VoteController extends ControllersParent{

    public function hasVoted(Poll $poll, Post $post, User $user):bool{
        try {
            $q = $this->database->prepare("SELECT * FROM `voice` WHERE `poll_id`=? AND `post_id`=? AND `user_id`=?");
            $q->execute(array($poll->getId(), $post->getId(), $user->getId()));
            return ($q->rowCount() > 0) ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function vote(Poll $poll, Post $post, Candidate $candidate, User $user):bool{
        try {
            
            if($this->hasVoted($poll, $post, $user)){
                return false;
            }

            // Insert the vote
            $q = $this->database->prepare("INSERT INTO `voice`(`poll_id`, `post_id`, `candidate_id`, `user_id`, timestamp) VALUES(?,?,?,?, NOW())");
            return ($q->execute(array($poll->getId(), $post->getId(), $candidate->getId(), $user->getId()))) ? true : false;
                       
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function voteWIthCard(Poll $poll, Post $post, Candidate $candidate, Card $card):bool{
        try{
            if($card->isUsed()){
                return false;
            }

            // code to clean double copy
            $q = $this->database->prepare("INSERT INTO  `voice`(`poll_id`, `post_id`, `candidate_id`, `card_code`, timestamp) VALUES(?,?,?,?, NOW())");
            return ($q->execute(array($poll->getId(), $post->getId(), $candidate->getId(), $card->get_code_card()))) ? true : false;
        }catch(\Throwable $error){
            return false;
        }
    }

    /**
     * It allows a user linked to a card to vote , 
     * @return bool
     */
    public function voteUserCardMode(Poll $poll, Post $post, Candidate $candidate, Card $card, User $user):bool{
        // check: is the user linked to the card and if card used
        if($this->hasVoted($poll, $post, $user) || !$card->isLinkable() || !$card->getLinkedUser() == $user->getId()){
            return false;
        }
        $q = $this->database->prepare("INSERT INTO  `voice`(`poll_id`, `post_id`, `candidate_id`, `card_code`,user_id, timestamp) VALUES(?,?,?,?,?,NOW())");
        return ($q->execute(array($poll->getId(), $post->getId(), $candidate->getId(), $card->get_code_card(), $user->getId()))) ? true : false;
    }

    public function startVote(Poll $poll):bool{
        $q = $this->database->prepare("UPDATE `poll` SET `status`=? WHERE `id`=?");
        return ($q->execute(array("in_progress", $poll->getId()))) ? true : false;
    }

    public function isVoteInProgress(Poll $poll):bool{
        $q = $this->database->prepare("SELECT * FROM `poll` WHERE `id`=? AND `status`=?");
        $q->execute(array($poll->getId(), "in_progress"));
        return ($q->rowCount() > 0) ? true : false;
    }

    public function endVote(Poll $poll):bool{
        $q = $this->database->prepare("UPDATE `poll` SET `status`=? WHERE `id`=?");
        return ($q->execute(array("passed", $poll->getId()))) ? true : false;
    }


}