<?php 
NAMESPACE Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');

use BcMath\Number;
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
use Dls\Evoting\controllers\ResultController;
use Dls\Evoting\controllers\CardController;


use DateTime;
use Exception;

use function PHPSTORM_META\type;

class Controller {

    private PollController $pollController; 

    private PostController $postController;

    private UsersController $usersController;

    private CandidateController $candidateController;

    private VoteController $voteController;

    private ResultController $resultController;

    private CardController $cardController;

    public function __construct() {
        $this->pollController = new PollController();
        $this->postController = new PostController();
        $this->usersController = new UsersController();
        $this->candidateController = new CandidateController();
        $this->voteController = new VoteController();
        $this->resultController = new ResultController();
        $this->cardController = new CardController();
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

    public function createUsersFromCsvData():array{
        if (!isset($_FILES['csvfile']))
            return[
                'status'=> "fail",
                "message"=>"Invalid file name. Please upload a CSV file named csvfile"
            ];

        $fileTmpPath = $_FILES['csvfile']['tmp_name'];
        $fileName = $_FILES['csvfile']['name'];
        $fileType = $_FILES['csvfile']['type'];

        
        $allowedMimeTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/octet-stream'];
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['csv'];
        
        
        if (in_array($fileType, $allowedMimeTypes) || in_array($ext, $allowedExt)) {
            if (($handle = fopen($fileTmpPath, 'r')) !== false) {
                $r = $this->usersController->createUsersFromCvsFile($handle);
                fclose($handle);
                return $r;
            } else {
                return[
                    'status'=> "fail",
                    "message"=>"Error opening the uploaded file."
                ];
            }
        }
        return[
            'status'=> "fail",
            "message"=>"Invalid file type. Please upload a CSV file"
        ];
    }

    public function getResultOfPoll(int $pollId){
        $poll = $this->pollController->getPoll($pollId);
        if (!($poll instanceof Poll)) {
            return [
                "status" => "fail",
                "message" => "unregonize id poll"
            ];
        }

        // if (!$this->pollController->isPollPassed($poll)) {
        //     return [
        //         "status" => "fail",
        //         "message" => "Le scrutin n'est pas encore terminé"
        //     ];
        // }

        $results = $this->resultController->getResultsByPollId($poll);

        return [
            "status" => "success",
            "message" => $results, 
            "poll_title" => $poll->getTitle()
        ];
    }

    /**
     * return the pdf result of a poll
     * @param int $pollID
     * @return array{message: string, status: string}
     */
    public function getResultPollPdf(int $pollID){
        $poll = $this->pollController->getPoll($pollID); 
        if(!$poll instanceof Poll){
            return[
                'status'=> 'fail', 
                'message'=>'Unkownpoll'
            ];
        }

        return $this->resultController->getResultPollPdf($poll);
    }

    public function getUserImage(int $userId){
        $user = $this->usersController->getUserById($userId);
        if($user instanceof User){
            return $this->usersController->getUserImage($user);
        }else{
            header("Content-Type:application/json");
            return [
                "status"=>"fail",
                "message"=>"Unfounded user with $userId id"
            ];
        }
    }

    public function postUserImage($userId, $file): array{
        $user = $this->usersController->getUserById($userId);

        if (!$user instanceof User) {
            return [
                'status' => 'fail',
                'message' => 'Utilisateur introuvable pour l\'ID fourni.'
            ];
        }

        if ($this->usersController->uploadUserImage($user, $file)) {
            return [
                'status' => 'success',
                'message' => 'Upload réussi !'
            ];
        }

        return [
            'status' => 'fail',
            'message' => 'Erreur lors de l\'upload.'
        ];
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

    public function getPollsOrderedByStatus():array{
        return [
            "status"=>"success", 
            "message"=>$this->pollController->getAllOrderByStatus()
        ];
    }

    public function getPoll($id):array{
        return [
            "status"=> "success",
            "message"=>$this->pollController->getPoll(intval($id))
        ];
    }

    public function getPollObject($id){
        return $this->pollController->getPoll(intval($id));
    } 
    
    public function deletePoll(int $pollId):array{
        $poll1 = $this->pollController->deletePoll($pollId);
        if($poll1 instanceof Poll){
            return [
                "status"=>"success", 
                "message"=>"poll deleted", 
                "poll" => $poll1
            ];
        }else{
            $poll = $this->getPollObject($pollId);
            if(!$poll instanceof Poll){
                return [
                    "status"=>"fail",
                    "message"=>"check the id you passed",
                ];
            }else{
                return[
                    "status"=>"fail", 
                    "message"=>"there is an internal error", 
                    "pollDelete res"=>$poll1
                ];
            }
        }
    }

    public function generateCardsForPoll(int $pollId,int $number):bool{
        $poll = $this->getPollObject($pollId);
        if($poll instanceof Poll){
            if($poll->getInCardMode()){
                $this->cardController->deleteCardOfPoll($poll);
                return $this->cardController->generateCardForPoll($poll, $number);
            }
            return $this->cardController->generateCardForPoll(poll:$poll, n:$number);
        }else{
            return false;
        }
    }

    public function getAvablePostForCard(int $pollId,  $codeCard):array{
        $poll = $this->getPollObject($pollId);
        $card = $this->cardController->getCardByCode($codeCard);

        if($poll instanceof Poll && $card instanceof Card){
            $res =  $this->postController->getAvablePostForCard($poll, $card);
            if($res instanceof Post){
                return [
                    "status"=>"success", 
                    "post"=>$res
                ];
            }else{
                return [
                    "status"=>"fail", 
                    "message"=>$res, 
                ];
            }
        }else{
            return [
                "status"=>"fail", 
                "message" => "you should chek your pollid and Code card passed"
            ];
        }
    }

    public function validateCardCodeForPoll(int $pollId, string $code_card):array|bool{
        $poll = $this->pollController->getPoll($pollId);
        if($poll instanceof Poll){
            if(!$poll->getInCardMode()){
                return false;
            }
            $card = $this->cardController->getCardByCode($code_card);
            
            if($card instanceof Card){
                if($this->cardController->isValidCardForPoll($poll, $card)){
                    if($this->postController->getAvablePostForCard(poll:$poll, card:$card) instanceof Post){
                        return [
                            "status"=>"success", 
                            "message"=>"the card is valid for this poll"
                        ];
                    }else{
                        return [
                            "status"=>"fail", 
                            "message"=>"Carte utilisee mais valide", 
                            "avai"=>$this->postController->getAvablePostForCard(poll:$poll, card:$card) 
                        ];
                    }
                }else{
                    return [
                        "status"=>"fail", 
                        "message"=>"the card is not valid for this poll"
                    ];
                }
            }
            return false;
        }else{
            return false;
        }
    }

    /**
     * 
     * set the poll in the mode of card vote
     */
    public function setToCardMode($idPoll, $cardsNumber):array{

        if(!is_numeric($idPoll) && !is_numeric($cardsNumber)){return ["status"=>"fail", "message"=>"all must be interger"];}

        $poll = $this->pollController->getPoll($idPoll);

        if($poll instanceof Poll){

            if($poll->getInCardMode() == true){
                $gen = $this->generateCardsForPoll((int) $idPoll, (int) $cardsNumber);
                return [
                    "status"=>"success", 
                    "message"=>[
                        "has_card_mode"=> true,
                        "generated"=>$gen,
                        "id"=>$idPoll,
                        "cardnumber"=>(int) $cardsNumber,
                        "card"=>$this->cardController->getCardOfPoll($poll)
                        ]
                    ];
            }

            $cardToPoll = $this->pollController->setPollToCardMode($poll);

            if(!$cardToPoll){
                return[
                    "status"=>"fail", 
                    "message"=>"internal error"
                ];
            }
            $this->generateCardsForPoll($idPoll, $cardsNumber);
            return [
                "status"=>"success", 
                "message"=>[
                    "has_card_mode"=> $cardToPoll,
                    "card"=>$this->cardController->getCardOfPoll($poll)
                ]
            ];
        }else{
            return[
                "status"=>"fail", 
                "message"=>"unrecognizeble poll id passed"
            ];
        }

    }

    /**
     * set a poll to user_linked_card mode
     * @param mixed $idPoll
     * @param mixed $cardsNumber
     * @throws Exception
     * @return array{message: string, status: string|array{status: string}}
     */
    public function setToUserLinkedCardMode($idPoll, $cardsNumber):array{
        try {
            if(!is_int($idPoll) && !is_int($cardsNumber))
            return[
                "status"=>"fail", 
                "message"=>"Id poll and card number must be intergers"
            ];

            $poll = $this->pollController->getPoll((int) $idPoll);

            // generate cards for poll
            $this->cardController->generateCardForPoll($poll, $cardsNumber);

            // check existance of poll
            if(!$poll instanceof Poll) return ['status'=>'fail', 'message'=>'Poll not found'];

            // return the card already linked 
            if($this->pollController->setPollToUserCardLinkVote($poll)) return ['status'=>'success', $this->cardController->getCardOfPoll($poll)];

            // -------------- erreur a reecrir ------
            return[
                'status'=>'fail', 
                'message'=>'unkown error'
            ];

        } catch (\Throwable $th) {
            throw new Exception("A Setting error occurs", 500 );
        }   
    }

    /**
     * link a user too a card
     * @param mixed $id_user
     * @return void
     */
    public function linkUserToCard($id_user, $idPoll):array{
        try {
            if(!is_int($id_user) || !is_int($idPoll)) return ['status'=>'fail', 'message'=>'user id and id poll must be interger']; 
    
            $user = $this->usersController->getUserById($id_user);
            $poll = $this->pollController->getPoll($idPoll);
    
            if(!$user instanceof User || !$poll instanceof Poll ) return ['status'=>'fail', 'message'=>'user or poll unfound']; 

            $card = $this->cardController->linkUserToCard($user, $poll);

            return ($card instanceof Card)? ['status'=>'success', 'message'=>$card]:['status'=>'fail', 'message'=>$card];
        } catch (\Throwable $e) {
            return [
                'status'=>'fail', 
                'message'=> $e->getMessage()
            ];
        }


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
                if ($this->usersController->isUserExist($user) && $this->usersController->isAdmin($user)) {
                    $result = $this->pollController->addPoll($title, $date_start, $date_end, $description);
                    return is_array($result) ? $result : [
                        "status" => "fail",
                        "message" => "Erreur lors de la création du scrutin"
                    ];
                } else {
                    return [
                        "status"=> "fail", 
                        "message"=>"you are not and admin", 
                    ];
                }
            }

        }
        
        return [
               "status"=>"fail", 
               "message"=> "elements not fulfied", 
               "data"=>$_POST,
          ];

    }

    public function generatePdfTemporaryFileCardForPoll(Poll $poll){
        if($poll instanceof \Dls\Evoting\models\Poll){
            if(!$poll->getInCardMode()){
                header('Content-Type: application/json');
                http_response_code(400);
                return json_encode([
                    "status" => "fail",
                    "error" => 'Le scrutin n\'est pas en mode ticket de vote',
                ]);
            }
            try {
                return $this->cardController->generatePdfTempFileCardForPoll($poll);
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                return json_encode([
                    'success' => false,
                    'error' => 'Erreur lors de la génération du PDF',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }
        return $poll;
    }

    public function getAllCandidate():array{
        return $this->candidateController->getAll();
    }

    public function getAvailbleUsersCandidatePoll($idPoll):array{
        if(is_numeric($idPoll)){
            $poll = $this->pollController->getPoll(intval($idPoll));
            if($poll instanceof \Dls\Evoting\models\Poll){
                return[
                    "status"=>"success", 
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

    // ____________----------------------- vote methods

    public function isVoteAuthorized(Poll $poll):array{
        return [
            "status"=> "success",
            "message"=> $this->pollController->isVotePollAuthorized($poll)
        ];
    }

    public function startVote(int $Idpoll):array{
        $poll = $this->pollController->getPoll($Idpoll);
        if(!($poll instanceof Poll)){
            return [
                "status"=>"fail",
                "message"=>"unregonize id poll"
            ];
        }

        return ($this->pollController->isPollPassed($poll))? 
            [
                "status"=>"fail",
                "message"=>"le scrutin est deja passer"
            ]
            :
            ($this->voteController->startVote($poll)? 
                [
                    "status"=>"success",
                    "message"=>"le scrutin a demarrer"
                ]:
                [
                    "status"=>"fail",
                    "message"=>"une erreur interne est survenue"
                ]);
    }

    public function endVote(int $idPoll):array{
        $poll = $this->pollController->getPoll($idPoll);
        if(!($poll instanceof Poll)){
            return [
                "status"=>"fail",
                "message"=>"unregonize id poll"
            ];
        }
        return ($this->pollController->isPollPassed($poll))? 
            [
                "status"=>"fail",
                "message"=>"le scrutin est deja passer"
            ]
            :
            ($this->voteController->endVote($poll)? 
                [
                    "status"=>"success",
                    "message"=>"le scrutin a ete cloturer"
                ]:
                [
                    "status"=>"fail",
                    "message"=>"une erreur interne est survenue"
                ]);
    }

    /**
     * write a vote for these params
     * @return array
     */
    public function vote():array
    {
        if (
            isset($_POST['poll_id'], $_POST['post_id'], $_POST['candidate_id'], $_POST['user_id']) &&
            is_numeric($_POST['poll_id']) &&
            is_numeric($_POST['post_id']) &&
            is_numeric($_POST['candidate_id']) &&
            is_numeric($_POST['user_id'])
        ) {
            $poll = $this->pollController->getPoll((int)$_POST['poll_id']);
            $post = $this->postController->getPostById((int)$_POST['post_id']);
            $candidate = $this->candidateController->getCandidate((int)$_POST['candidate_id']);
            $user = $this->usersController->getUserById((int)$_POST['user_id']);

            if (
                $poll instanceof Poll &&
                $post instanceof Post &&
                $candidate instanceof Candidate &&
                $user instanceof User
            ) {

                if($poll->getInCardMode()){
                    return [
                        "status" => "fail",
                        "message" => "Ce strutin est en mode vote avec card"
                    ];
                }

                if ($this->pollController->isPollPassed($poll)) {
                    return [
                        "status" => "fail",
                        "message" => "Le scrutin est déjà passé"
                    ];
                }

                if($this->voteController->hasVoted($poll, $post, $user)){
                    return [
                        "status" => "fail",
                        "message" => "Vous avez déjà voté pour ce poste dans ce scrutin"
                    ];
                }

                $result = $this->voteController->vote($poll, $post, $candidate, $user);

                if ($result) {
                    return [
                        "status" => "success",
                        "message" => "Vote enregistré"
                    ];
                } else {
                    return [
                        "status" => "fail",
                        "message" => "Erreur lors de l'enregistrement du vote"
                    ];
                }
            } else {
                return [
                    "status" => "fail",
                    "message" => "Paramètres invalides"
                ];
            }
        } else {
            return [
                "status" => "fail",
                "message" => "Paramètres manquants"
            ];
        }
    }

    /**
     * write a vote for these params
     * @return array
     */
    public function voteWithUserLinkedCard():array
    {
        if (
            isset($_POST['poll_id'], $_POST['post_id'], $_POST['candidate_id'], $_POST['user_id']) &&
            is_numeric($_POST['poll_id']) &&
            is_numeric($_POST['post_id']) &&
            is_numeric($_POST['candidate_id']) &&
            is_numeric($_POST['user_id'])
        ) {
            $poll = $this->pollController->getPoll((int)$_POST['poll_id']);
            $post = $this->postController->getPostById((int)$_POST['post_id']);
            $candidate = $this->candidateController->getCandidate((int)$_POST['candidate_id']);
            $user = $this->usersController->getUserById((int)$_POST['user_id']);

            if (
                $poll instanceof Poll &&
                $post instanceof Post &&
                $candidate instanceof Candidate &&
                $user instanceof User
            ) {

                if($poll->getInCardMode()){
                    return [
                        "status" => "fail",
                        "message" => "Ce strutin est en mode vote avec card"
                    ];
                }

                if ($this->pollController->isPollPassed($poll)) {
                    return [
                        "status" => "fail",
                        "message" => "Le scrutin est déjà passé"
                    ];
                }

                if($this->voteController->hasVoted($poll, $post, $user)){
                    return [
                        "status" => "fail",
                        "message" => "Vous avez déjà voté pour ce poste dans ce scrutin"
                    ];
                }

                $result = $this->voteController->vote($poll, $post, $candidate, $user);

                if ($result) {
                    return [
                        "status" => "success",
                        "message" => "Vote enregistré"
                    ];
                } else {
                    return [
                        "status" => "fail",
                        "message" => "Erreur lors de l'enregistrement du vote"
                    ];
                }
            } else {
                return [
                    "status" => "fail",
                    "message" => "Paramètres invalides"
                ];
            }
        } else {
            return [
                "status" => "fail",
                "message" => "Paramètres manquants"
            ];
        }
    }

     /**
     * write a vote for these params in Card mode poll
     * @return array
     */
    public function voteInCardMode():array
    {
        if (
            isset($_POST['poll_id'], $_POST['post_id'], $_POST['candidate_id'], $_POST['card_code']) &&
            is_numeric($_POST['poll_id']) &&
            is_numeric($_POST['post_id']) &&
            is_numeric($_POST['candidate_id']) &&
            !empty($_POST['card_code'])
        ) {
            $poll = $this->pollController->getPoll((int)$_POST['poll_id']);
            $post = $this->postController->getPostById((int)$_POST['post_id']);
            $candidate = $this->candidateController->getCandidate((int)$_POST['candidate_id']);
            $card = $this->cardController->getCardByCode($_POST['card_code']);

            if (
                $poll instanceof Poll &&
                $post instanceof Post &&
                $candidate instanceof Candidate &&
                $card instanceof Card
            ) {
                if ($this->pollController->isPollPassed($poll)) {
                    return [
                        "status" => "fail",
                        "message" => "Le scrutin est déjà passé"
                    ];
                }

                if(!$poll->getInCardMode()){
                    return [
                        "status" => "fail",
                        "message" => "Ce strutin n'est pas en mode de vote avec ticket de vote"
                    ];
                }

                // if the card is valide beacause of double data error
                if(!$this->postController->getAvablePostForCard($poll, $card) instanceof Post){
                    return [
                        "status" => "fail", 
                        "message" => "Card allready used"
                    ];
                }

                $result = $this->voteController->voteWIthCard($poll, $post, $candidate, $card);

                if ($result) {
                    return [
                        "status" => "success",
                        "message" => "Vote enregistré", 
                        "candidate" => $candidate
                    ];
                } else {
                    return [
                        "status" => "fail",
                        "message" => "Erreur lors de l'enregistrement du vote"
                    ];
                }
            } else {
                return [
                    "status" => "fail",
                    "message" => "Paramètres invalides", 
                    "data"=>[
                        $candidate, 
                    ]
                ];
            }
        } else {
            return [
                "status" => "fail",
                "message" => "Paramètres manquants"
            ];
        }
    }

    public function isVoteInProgress(int $idPoll):array{
        $poll = $this->pollController->getPoll($idPoll);
        if(!($poll instanceof Poll)){
            return [
                "status"=>"fail",
                "message"=>"unregonize id poll"
            ];
        }
        return ($this->voteController->isVoteInProgress($poll))? 
            [
                "status"=>"success",
                "message"=>"le scrutin est en cours"
            ]:
            [
                "status"=>"fail",
                "message"=>"le scrutin n'est pas en cours"
            ];
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