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
use Dls\Evoting\controllers\CandidateController;

use DateTime;

class Controller {

    private PollController $pollController; 

    private PostController $postController;

    private UsersController $usersController;

    private CandidateController $candidateController;

    private VoteController $voteController;

    public function __construct() {
        $this->pollController = new PollController();
        $this->postController = new PostController();
        $this->usersController = new UsersController();
        $this->candidateController = new CandidateController();
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
            "message"=>array_reverse($this->pollController->getAll())
        ];
    }

    public function getPoll($id):array{
        return [
            "status"=> "success",
            "message"=>$this->pollController->getPoll(intval($id))
        ];
    }

    public function addPoll():array{

        if(isset($_POST, $_POST["title"], $_POST["date_start"], $_POST["description"], $_POST["date_end"], $_POST["user_id"])){
            $title = $_POST["title"]; 
            $description = $_POST["description"];
            $date_start = new DateTime(str_replace("T", " ", $_POST["date_start"])); 
            $date_end = new DateTime(str_replace("T", " ", $_POST["date_end"]));
            $userId = $_POST["user_id"];

            if(!empty($title) && !empty($description) && !empty($date_start) && !empty($date_end) && !empty($userId)){ 
            
                $user = $this->usersController->getUserById($userId);
                return ($this->usersController->isUserExist($user) && $this->usersController->isAdmin($user))? [
                    "status"=> "success", 
                    "message"=>$this->pollController->addPoll($title, $date_start, $date_end, $description),
                ]: [
                    "status"=> "fail", 
                    "message"=>"you are not and admin", 
                ];
            }

        }
        
        return [
               "status"=>"fail", 
               "message"=> "elements not fulfied", 
               "data"=>$_POST,
          ];

    }

    public function getAvailbleUsersCandidatePoll($idPoll):array{
        if(is_numeric($idPoll)){
            $poll = $this->pollController->getPoll(intval($idPoll));
            if($poll instanceof \Dls\Evoting\models\Poll){
                return[
                    "status"=>"succes", 
                    "message"=>$this->usersController->getUserAvaibleCandidatePoll($poll),
                ];
            }else{
                return [
                    "status"=>"fail",
                    "message"=>"unregonize id poll", 
                    "data"=>$poll
                ];
            }
        }else{
            return [
                "status"=>"fail",
                "message"=>"unregonize id poll"
            ];
        }
    }

    /**
     * must pass the id of the poll (idPoll) and the title (title) of the new post
     */
    public function addPost():array{
        if(isset($_POST["idPoll"]) && isset($_POST["title"])){
            $idPoll = $_POST["idPoll"];
            $title = $_POST["title"];
            if(is_numeric($idPoll) ){
                $poll = $this->pollController->getPoll($idPoll);
                if($poll instanceof \Dls\Evoting\models\Poll){
                    return[
                        "status"=>"succes",
                        "message"=>($this->postController->addPost($poll, $title) != false)? "donne": "there is internal error"
                    ];
                }
                return[
                    "status"=>"fail", 
                    "message"=>"unknwon poll or invalid poll type"
                ];
            }
            return[
                "status"=>"fail", 
                "message"=>"Param error", 
                "params"=>$_POST
            ];
        }
        return[
            "status"=>"fail", 
            "message"=>"unfound params"
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

    // -------------- Candidate methods ---------------
    public function addCandidateToPost(int $idUser, int $idPost,int $pollId):array{
        return (is_numeric($idUser) && is_numeric($idPost) && is_numeric($pollId)) ? 
            ($this->candidateController->addCandidateToPost($idUser, $idPost, $pollId) ? 
                [
                    "status"=>"success",
                    "message"=>"candidate added"
                ]:
                [
                    "status"=>"fail",
                    "message"=>"internal error"
                ])
            :
            [
                "status"=>"fail",
                "message"=>"param error"
            ];
    }

    public function changeCandidateState(int $idCand, int $idPost):array{
        return (is_numeric($idCand) && is_numeric($idPost)) ? 
            ($this->candidateController->changeCandidateStateToReject($idCand, $idPost) ? 
                [
                    "status"=>"success",
                    "message"=>"candidate state changed"
                ]:
                [
                    "status"=>"fail",
                    "message"=>"internal error"
                ])
            :
            [
                "status"=>"fail",
                "message"=>"param error"
            ];
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