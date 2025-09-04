<?php
namespace Dls\evoting\Api;

use DateTime;

require_once(__DIR__ . '/../vendor/autoload.php');


use Dls\Evoting\controllers\Controller;


class Api
{
    private Controller $controller;

    private String $base_url = "/evoting/api/";

    private array $route = [];

   public function __construct(){
        $this->controller = new Controller();
        
        // hearders
        header("Content-type: Application/json");

        // -------adding routes ------------
        
        // *********** get routes **********

        // get pool enPoints
        $this->pollEndPoint();

        // posts endpoints
        $this->postEndPoints();

        // user endPont
        $this->usersEnpoints();

        // statiscs endpoint
        $this->statsEndPoints();

        // candidate endPoint
        $this->candidatesEndpoints();

        // adding the handle requests
        echo $this->handleRequest($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
   }

   private function pollEndPoint(){
    // return all the polls
    $this->add_roote("get", "polls", function():string{
        return json_encode($this->controller->getPolls());
    });


    // return one poll
    $this->add_roote("get", "poll/{id}", function(int $id){
        return json_encode($this->controller->getPoll($id));
    });

    // add a poll and informations required are userId, title, description, date_start, date_end
    $this->add_roote("POST", "poll", function (){
          return json_encode($this->controller->addPoll());
    });


//     $this->add_roote("update", "poll", function(){
//           return json_encode(["update"=>true])
//     });
   }

     /**
      * 
      */
     private function candidatesEndpoints(){
          // get the avaible candidates for posts of the given pool 
          $this->add_roote("get", "poll/avaible/candidate/{idPoll}", function($idPoll){
                    return json_encode($this->controller->getAvailbleUsersCandidatePoll(idPoll:$idPoll));
          });

          // change the state of the candidate (maybe : reject / remove, or accept)
          $this->add_roote("POST","candidate/state/change", function(){
               if(isset($_POST, $_POST["idCand"], $_POST["state"], $_POST["idPost"])){

                    if(!is_numeric($_POST["idCand"]) || !is_numeric($_POST["state"]) || !is_numeric($_POST["idPost"])){
                         return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer and the state must be 0, 1 or -1", "data"=>$_POST]);
                    }

                    $idCand = intval($_POST["idCand"]);
                    $state = intval($_POST["state"]);
                    $idPost = intval($_POST["idPost"]);
                    if($idCand > 0 && ($state === 0 || $state === 1 || $state === -1)){
                         return json_encode($this->controller->changeCandidateState($idCand, $idPost));
                    }
                    return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer and the state must be 0, 1 or -1", "data"=>$_POST]);
               }else{
                    return json_encode(["status"=>"fail", "message"=>"you must post data {idCand, state, idPost}", "data"=>$_POST]);
               }
          });

          // add a candidate to a post of a poll with this post data : user_id, post_id, poll_id
          $this->add_roote("POST", "candidate/add", function(){
               if(isset($_POST, $_POST["user_id"], $_POST["post_id"], $_POST["poll_id"])){
                    $idUser = intval($_POST["user_id"]);
                    $idPost = intval($_POST["post_id"]);
                    $idPoll = intval($_POST["poll_id"]);
                    if($idUser > 0 && $idPost > 0 && $idPoll > 0){
                         return json_encode($this->controller->addCandidateToPost($idUser, $idPost, $idPoll));
                    }
                    return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
               }else{
                    return json_encode(["status"=>"fail", "message"=>"you must post data {user_id, post_id, poll_id}", "data"=>$_POST]);
               }
          });
     }

   private function postEndPoints(){
     // add post endpoint
     $this->add_roote("POST", "post/add", function (){
          return json_encode($this->controller->addPost());
     });
   }

   private function usersEnpoints(){
     // creation count
     $this->add_roote("post","signup", function(){
          if(isset($_POST, $_POST["name"], $_POST["matricule"], $_POST["email"], $_POST["rfid"])){
               $name = $_POST["name"];
               $matricule = $_POST["matricule"];
               $email = $_POST["email"];
               $rfid = $_POST["rfid"];
               return json_encode($this->controller->createAcountUser($name,$matricule ,$email,$rfid));
          }
          return json_encode(["status"=>"fail", "message"=>"you must post data {name, matricule, email, rfid}", $_POST]);

     });

     // get users
     $this->add_roote("get", "users/all",function(){
          return json_encode($this->controller->getUsers());
     } );

   }

   private function statsEndPoints(){
     $this->add_roote("get", "statistics", function(){
          return json_encode($this->controller->getStatistics());
     });
   }

//    private function getImagesEndPoints(){
//      // get the first image of the code_owner from the system
//      $this->add_roote("get", "api/{token_user}/image/{code_image_owner}",function($token_user, string $code_image_owner){
//           return ($this->controller->is_token_valid($token_user))? 
//                $this->controller->get_image($code_image_owner):
//                $this->badTokenError($token_user);
//      });

//      // get the image code_owner from the system or a specific image by its index 
//      $this->add_roote("get", "api/{token_user}/image/{code_image_owner}/{image_index}",function($token_user, string $code_image_owner, int $image_index = 0){
//           return ($this->controller->is_token_valid($token_user))? 
//                $this->controller->get_image($code_image_owner, $image_index):
//                $this->badTokenError($token_user);
//      });
//    }

   // ******* Post methods **********
//    private function postHousesEndpoint(){
//      // add a new house
//      $this->add_roote("POST","api/{token_user}/user/houses/add/{code_user}/{user_password}", function($token_user, String $code_user, String $user_password){
//           return ($this->controller->is_token_valid($token_user))? 
//                $this->controller->add_house($code_user, $user_password):
//                $this->badTokenError($token_user);
//      });
//    }
   

   /**
    * add an URL to URLs of our routes
    * @param string $method
    * @param string $path
    * @param callable $callable
    * @return void
    */
     private function add_roote(string $method, string $path, callable $callable):void{
          $this->route [] = [
               "method" => strtoupper($method),
               "path" => $this->base_url . $path,
               "callback" => $callable
          ];
     }


     /**
      * handles the request
      * @param mixed $method
      * @param mixed $uri
      */
     private function handleRequest($method, $uri) {
          foreach ($this->route as $r) {
               if ($r['method'] === strtoupper($method) && preg_match($this->convertPathToRegex($r['path']), $uri, $matches)) {
                    array_shift($matches);
                    http_response_code(200);
                    return call_user_func_array($r['callback'], $matches);
               }
          }
          return $this->notFound();
     }

     private function convertPathToRegex($path) {
          return '#^' . preg_replace('/\{(\w+)\}/', '([^/]+)', $path) . '$#';
     }

     /**
      * return an array of a note found endpoint error
      * @return bool|string
      */
     private function notFound(){
          http_response_code(404);
          return json_encode(["error"=>"endPoint not found"]);
     }

     /**
      * this return an error if the token is invalid
      * @param string $token
      * @return bool|string
      */
     private function badTokenError(string $token){
          http_response_code(404);
          return json_encode(["error"=>"Invalid token passed"]);
     }
 
}

// echo ("/evoting/app/" . "api/polls  </br>");
// echo ($_SERVER["REQUEST_URI"]);


new Api();
