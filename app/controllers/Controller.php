<?php 
NAMESPACE Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');


use Dls\Evoting\models\Poll;
use Dls\Evoting\models\User;
use Dls\Evoting\models\Voice;
use Dls\Evoting\models\Post; 
use Dls\Evoting\models\Result; 
use Dls\Evoting\models\Card;
use Dls\Evoting\models\Candidate;

use Dls\Evoting\controllers\PollController;
use Dls\Evoting\controllers\PostController; 
use Dls\Evoting\controllers\UsersController;
use Dls\Evoting\controllers\VoteController;

use DateTime;

class Controller {

    private PollController $pollController; 

    private PostController $postController;

    private UsersController $usersController;

    private VoteController $voteController;

    public function __construct() {
        $this->pollController = new PollController();
        $this->postController = new PostController();
        $this->usersController = new UsersController();
        // $this->voteController = new VoteController();
    }

    // ************___________ user methods ______________************
    public function createAcountUser(string $name, string $matricule, string $email, string $rfid):array{
        
        if(!empty($matricule) && !empty($email) && !empty($rfid) &&
            trim($name) != "" && trim($email) != ""&& trim($rfid) != ""
        ){
            $rep = $this->usersController->createUser(matricule:$matricule, email:$email,name:$name, rfid:$rfid); 

            if($rep != false){
                return[
                    "status"=> "success",
                    "message"=> $rep
                ];
            }else{
                return [
                    "status"=> "fail",
                    "message"=>'quelque chose ne va pas bien'
                ];
            }

        }else{
            return[
                'status'=> "fail",
                "message"=>"tout les champs doivent etre remplits"
            ];
        }
        
    }

    public function getUsers():array{
        return[
            "status"=> "succes",
            "message"=> $this->usersController->getAll(),
        ];
    }

    public function logCard():array{
        return [
            "status"=> "succes",
            "message"=> "card is good"
        ]; 
    }

    // __________********** poll methods *********___________

    public function getPolls():array{
        return [
            "status"=>"success", 
            "message"=>$this->pollController->getAll()
        ];
    }

    public function getPoll($id):array{
        return [
            "status"=> "success",
            "message"=>$this->pollController->getPoll(intval($id))
        ];
    }

    public function addPoll(User $user, string $title, DateTime $date_start, DateTime $date_end, string $description):array{
        return ($this->usersController->isUserExist($user) && $this->usersController->isAdmin($user))? [
            "status"=> "success", 
            "message"=>$this->pollController->addPoll($title, $date_start, $date_end, $description),
        ]: [
            "status"=> "fail", 
            "message"=>"you are not and admin", 
        ];
    }

    // vote methods

    public function isVoteAuthorized(Poll $poll):array{
        return [
            "status"=> "success",
            "message"=> $this->pollController->isVotePollAuthorized($poll)
        ];
    }

    /**
     * write a vote for these params
     * @param \Dls\Evoting\models\Poll $poll
     * @param \Dls\Evoting\models\Post $post
     * @param \Dls\Evoting\models\Candidate $candidate
     * @param \Dls\Evoting\models\User $user
     * @return array
     */
    public function vote(Poll $poll, Post $post, Candidate $candidate, User $user):array{
        if(!$this->pollController->isPollPassed($poll)){
            return [];
        }
        return [];
    }

    public function getResult(Poll $poll, Post $post):array{
        if($this->pollController->isPollPassed( $poll)){
            return [];
        }
        return [];
    }



    // statisfunction of things here

    /**
     * return and array that contains the global stats about
     * polls: done, notdone, number of participant, participant perpoll, 
     * @return array
     */
    public function getStatistics():array{
        return[
            "status"=> "success",
            "message"=>[
                "poll"=>$this->pollController->getStats(),
                "posts"=>$this->postController->getStats(), 
                "users"=> $this->usersController->getStats()
            ],
        ];
    }

}