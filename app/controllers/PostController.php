<?php 
namespace Dls\Evoting\controllers;

// imports 
require_once(__DIR__ . '/../../vendor/autoload.php');

use PDO;

use Dls\Evoting\controllers\ControllersParent;

use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post;
use Dls\Evoting\models\User;
use Dls\Evoting\models\Card;


class PostController extends ControllersParent{

    /**
     * return all the post in an array
     * @return Post[]
     */
    public function getAll():array{

        $pList = [];

        $q = $this->database->query("SELECT * FROM post");
        foreach( $q->fetchAll() as $post ){

            $candQ = $this->database->prepare("SELECT u.name, u.id, c.id as candId FROM `candidate` c LEFT JOIN `users` u ON `u`.`id` = `c`.`user_id` WHERE `c`.`post_id` = ?");
            $candQ->execute(array($post['id']));

            $pList[] = new Post($post['id'], $post['poll_id'], $post['post_name'], $candQ->fetchAll(PDO::FETCH_ASSOC));
        }

        return $pList;
    }

    public function getPostById(int $id):Post|bool{
        foreach($this->getAll() as $post){
            if($post->getId() == $id){
                return $post;
            }
        }
        return false;
    }

    /**
     * return all the post of the givien poll
     * @param \Dls\Evoting\models\Poll $poll
     * @return array
     */
    public function getPostOfPoll(Poll $poll):array{
        return $poll->get_posts();
    }

    /**
     * return the post for wich the user has vote
     * @param \Dls\Evoting\models\Poll $poll
     * @param \Dls\Evoting\models\User $user
     * @return bool
     */
    public function getCurrentPostForPollVote(Poll $poll, User $user):Post|bool{

        $q = $this->database->prepare("SELECT p.* FROM post p WHERE p.poll_id = ? 
            AND p.id NOT IN (
                SELECT v.post_id FROM voice v WHERE v.poll_id = ? AND v.user_id = ? 
            )
            ORDER BY p.id 
            LIMIT 1
            ");

        $q->execute(array($poll->getId(), $poll->getId(), $user->getId()));

        $ans = $q->fetchAll($this->database::FETCH_ASSOC);

        var_dump($ans);

        return false;
    }

    public function getAvablePostForCard(Poll $poll, Card $card):Post|array{
        try {
            $q = $this->database->prepare("SELECT * FROM `post` WHERE `post`.`poll_id` = ? AND `post`.`id` 
                                            NOT IN (SELECT `voice`.`post_id` FROM `voice` WHERE `voice`.`card_code` = ? ) ORDER BY `post`.`id` LIMIT 1");
            $q->execute(array($poll->getId(), $card->get_code_card()));
            $p = $q->fetch(PDO::FETCH_ASSOC);

            if($q->rowCount() == 0){
                return [
                    "status"=>"success",
                    "rowCount"=>0,
                ];
            }

            $post = $this->getPostById($p["id"]);
    
            return $post;
        } catch (\Throwable $th) {
            return ["status"=>"fail","error"=>$th->getMessage()];
        }
    }

    /**
     * add the post of the poll
     * @param \Dls\Evoting\models\Poll $poll
     * @param string $post_name
     * @return bool|object|Poll[]
     */
    public function addPost(Poll $poll, string $post_name):array|bool{
        $q = $this->database->prepare("INSERT INTO post(poll_id, post_name) VALUES(?, ?)");
        if($post_name == ""){
            return false;
        }
        return ($q->execute(array($poll->getId(),$post_name)))? $this->getPostOfPoll($poll): false;
    }

    /**
     * delete a post from the database
     * @param \Dls\Evoting\models\Post $post
     * @return bool
     */
    public function removePost(Post $post):Post|bool{
        if($this->isPostExist($post)){
            $q = $this->database->prepare("DELETE FROM post WHERE id = ? ");
            return $q->execute(array($post->getId()));
        }
        return false;
    }


    // booleans 

    /**
     * return true if the post exist in the array of the poll's post
     * @param \Dls\Evoting\models\Poll $poll
     * @param \Dls\Evoting\models\Post $post
     * @return bool
     */
    public function isPostOfPoll(Poll $poll, Post $post):bool{
        return in_array($post, $this->getPostOfPoll($poll));
    }

    /**
     * return true if the post exist
     * @param \Dls\Evoting\models\Post $post
     * @return bool
     */
    public function isPostExist(Post $post):bool{
        return in_array($post,$this->getAll());
    }


    public function getStats():array{
        return[
            "posts"=>count($this->getAll()),
        ];
    }

}


