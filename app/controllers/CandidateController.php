<?php 

namespace Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;

class CandidateController extends  ControllersParent
{

    public function addCandidateToPost(int $idUser, int $idPost,int $pollId):bool{
        $q = $this->database->prepare("INSERT INTO `candidate`(`user_id`, `post_id`, `poll_id` , `status`) VALUES (?, ?, ?, 0)");
         return $q->execute(array($idUser, $idPost, $pollId)) ? true : false;
    }

    public function changeCandidateStateToReject(int $idCand, int $idPost):bool{
        $q = $this->database->prepare("UPDATE `candidate` SET `status`=? WHERE `user_id`=? AND `post_id`=?");
         return $q->execute(array(-1, $idCand, $idPost)) ? true : false;
    }
    
}

