<?php

NAMESPACE Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');


use PDO;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post;
use Dls\Evoting\models\User;
use Dls\Evoting\controllers\ControllersParent;
use DateTime;


/**
 * this is the class that follow the flow of interaction with the database
 * according to operation of pool
 */
class PollController extends ControllersParent{

    // get methods

    /**
     * return an arrau that contains all the poll objec
     * @return Poll[]
     */
    public function getAll():array|Object{
        $pollList = [];

        $q = $this->database->prepare("SELECT * FROM poll");
        $q->execute();

        while($p = $q->fetch(PDO::FETCH_ASSOC)){
            $poll = new Poll(
                id:$p['id'], 
                title:$p['title'],
                date_start: new DateTime(datetime:$p['date_start']),
                date_end: new DateTime(datetime:$p['date_end']), 
                status:$p['status'], 
                description:$p['description']);
            
            $this->loadPostPoll($poll);

            $pollList[] = $poll;
        }
        
        return $pollList;
    }

    /**
     * loads the posts of a pool
     * @param \Dls\Evoting\models\Poll $poll
     * @return void
     */
    private function loadPostPoll(Poll $poll):void{    
        $qPost = $this->database->prepare("SELECT * FROM post WHERE poll_id = ?");
        $qPost->execute(array($poll->getId()));

        $plist = [];

        while($p = $qPost->fetch(PDO::FETCH_ASSOC)){
            $post = new Post($p["id"], $p["poll_id"],$p["post_name"]);
            $plist[] = $post;
        }

        $poll->setPosts($plist);
            
    }

    /**
     * return the array of all the poll that their date is in the future
     * @return array<mixed|Poll>
     */
    public function getForFuture():array|Object{
        $l = [];

        foreach ($this->getAll() as $key => $value) {
            if($value->getDateStart()->getTimestamp() > $this->dateTime->getTimestamp()){
                $l[] = $value; 
            }
        }

        return $l;
    }

    /**
     * return the array of all the pool that has to be done
     * @return array<mixed|Poll>
     */
    public function getForNow():array|Object{
        $l = [];

        foreach ($this->getAll() as $key => $value) {
            if($value->getDateStart()->getTimestamp() <= $this->dateTime->getTimestamp() &&  $value->getDateEnd()->getTimestamp() >= $this->dateTime->getTimestamp()){
                $l[] = $value; 
            }
        }
        return $l;
    }

    /**
     * return the array of all the poll that has been passed
     * @return array<mixed|Poll>
     */
    public function getPassed():array|Object{
        $l = [];

        foreach ($this->getAll() as $key => $value) {
            if($value->getDateEnd()->getTimestamp() < $this->dateTime->getTimestamp() || 
                $value->getStatus() == "passed"
            ){
                $l[] = $value; 
            }
        }
        return $l;
    }

    /**
     * this method add a new poll in the database
     * @param string $title
     * @param \DateTime $date_start
     * @param \DateTime $date_end
     * @param string $description
     * @return null
     */

    public function getPoll(int $id):?Poll{
        foreach ($this->getAll() as $key => $value) {
            if($value->getId() == $id){
                return $value;
            }
        }
        return null;
    }

    // add methods 

    /**
     * creates a new pool
     * @param string $title
     * @param \DateTime $date_start
     * @param \DateTime $date_end
     * @param string $description
     * @return null
     */
    public function addPoll(string $title, DateTime $date_start, DateTime $date_end, string $description):Poll|null{
        if($date_start->getTimestamp() < $date_end->getTimestamp() && $date_start->getTimestamp() > $this->dateTime->getTimestamp()){
            $q = $this->database->prepare("INSERT INTO poll(title, date_start, date_end, status, description) VALUES(?,?,?,?,?)");
            $q->execute(array($title, $date_start->format("Y-m-d H:i:s"), $date_end->format("Y-m-d H:i:s"),"inactif", $description));
            $poll = $q->fetch(PDO::FETCH_ASSOC);
            
            var_dump($poll);

            return null;
        }
        return null;
    }

    // delete methods
    /**
     * delete a pool
     * @param int $id
     * @return bool|Poll|null
     */
    public function deletePoll(int $id):Poll|Bool{
        foreach ($this->getAll() as $key => $value) {
            if($value->getId() == $id){
                $pool = $this->getPoll($id);
                $q = $this->database->prepare("DELETE FROM poll WHERE id = ?");
                
                return $q->execute(array($id)) ? $pool: false;
            }
        }

        return false;
    }

    /**
     * return true if the vote has been passed
     * @param \Dls\Evoting\models\Poll $poll
     * @return bool
     */
    public function isPollPassed(Poll $poll):bool{
        return in_array($poll, $this->getPassed());
    }

    public function isVotePollAuthorized(Poll $poll):bool{
        return in_array($poll, $this->getForNow());
    }

    // edit methods 

    /**
     *  change the inatifs state to actif of the id of the poll passed in the params 
     * @param int $id the id of the poll
     * @return bool
     */
    public function startVoteSession(int $id):bool{
        // change the inatifs state to actif
        return false;
    }

    /**
     * change the state of the poll to passed
     * @param int $id the id of the poll 
     * @return void
     */
    public function stopVoteSession(int $id):bool{
        // change the state to passed
        return false;
    }
    
    /**
     * Change the state of the poll to inactif to make it new
     * @param int $id
     * @return bool
     */
    public function reinitialiteState(int $id):bool{
        // initialte the state to inactif
        return false;
    }
}