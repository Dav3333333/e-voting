<?php 

namespace Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;
use Dls\Evoting\models\Candidate;

class CandidateController extends  ControllersParent
{

    public function getAll():array{
        $q  = $this->database->query("SELECT * FROM candidate");
        return $q->fetchAll();
    }

    public function addCandidateToPost(int $idUser, int $idPost,int $pollId):bool{
        $q = $this->database->prepare("INSERT INTO `candidate`(`user_id`, `post_id`, `poll_id` , `status`) VALUES (?, ?, ?, 0)");
         return $q->execute(array($idUser, $idPost, $pollId)) ? true : false;
    }

    public function changeCandidateStateToReject(int $idCand, int $idPost):bool{
        $q = $this->database->prepare("UPDATE `candidate` SET `status`=? WHERE `user_id`=? AND `post_id`=?");
         return $q->execute(array(-1, $idCand, $idPost)) ? true : false;
    }

    public function changeCandidateStateToAccept(int $idCand, int $idPost):bool{
        $q = $this->database->prepare("UPDATE `candidate` SET `status`=? WHERE `user_id`=? AND `post_id`=?");
         return $q->execute(array(1, $idCand, $idPost)) ? true : false;
    }

    public function getCandidate(int $id):Candidate|bool{
        $q = $this->database->prepare("SELECT * FROM `candidate` WHERE `candidate`.`id`=?");
        $q->execute(array($id));
        $data = $q->fetch();
        if($data){
            return new Candidate($data['id'], $data['user_id'], $data['post_id'], $data['poll_id'], $data['status']);
        }
        return $data;
    }
    
}

