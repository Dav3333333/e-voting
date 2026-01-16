<?php
namespace Dls\evoting\Api;

use DateTime;

require_once(__DIR__ . '/../vendor/autoload.php');


use Dls\Evoting\controllers\Controller;
use Dls\Evoting\models\Poll;
use Exception;


class Api
{
    private Controller $controller;

    private String $base_url = "/evoting/api/";

    private array $route = [];

     public function __construct(){
          // Disable direct display of PHP errors (they would break JSON responses)
          ini_set('display_errors', '0');
          error_reporting(E_ALL);
          set_error_handler(function($severity, $message, $file, $line) {
               throw new \ErrorException($message, 0, $severity, $file, $line);
          });

          $this->controller = new Controller();

          // pdf application endpoint
          $this->pdfApplicationEndpoint();
        
          // adding the json application endpoint
          $this->jsonApplicationEndpoint();

          // images endpoint
          $this->getImagesEndPoints();

          // Handle requests and ensure output is valid JSON even on warnings/errors
          ob_start();
          try {
               $response = $this->handleRequest($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
               if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
               echo $response;
          } catch (\Throwable $e) {
               http_response_code(500);
               if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
               echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
          } finally {
               ob_end_flush();
          }
     }

    private function pdfApplicationEndpoint() {
          $this->add_roote("get", "poll/{idPoll}/cards/pdf", function($idPoll) {
               try {
                    $idPoll = intval($idPoll);
                    if ($idPoll <= 0) {
                         http_response_code(400);
                         header('Content-Type: application/json');
                         return json_encode(['error' => 'ID de scrutin invalide']);
                         exit;
                    }

                    $poll = $this->controller->getPollObject($idPoll);
                    if (!$poll) {
                         http_response_code(404);
                         header('Content-Type: application/json');
                         return json_encode(['error' => 'Scrutin non trouvé']);
                         exit;
                    }

                    // Vérifier les autorisations si nécessaire
                    if (!$this->hasAccessToPoll($poll)) {
                         http_response_code(403);
                         header('Content-Type: application/json');
                         return json_encode(['error' => 'Accès non autorisé']);
                         exit;
                    }

                    // Génère le PDF directement
                    return $this->controller->generatePdfTemporaryFileCardForPoll($poll);
                    exit;
                    
               } catch (Exception $e) {
                    error_log("API PDF Error: " . $e->getMessage());
                    http_response_code(500);
                    header('Content-Type: application/json');
                    return json_encode(['error' => 'Erreur interne du serveur']);
                    exit;
               }
          });
     }

     // Méthode utilitaire pour vérifier l'accès (à adapter selon votre auth)
     private function hasAccessToPoll(Poll $poll): bool {
          // Implémentez votre logique d'autorisation ici
          return true; // Temporaire
     }


   private function jsonApplicationEndpoint(){
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

     // cote endpoints
     $this->voteEndPoint();

     // result endPoint
     $this->resultEndPoints();
   }

   private function voteEndPoint(){
     // vote action with user count
     // $this->add_roote("POST", "vote", function(){
     //      if(isset($_POST, $_POST["user_id"], $_POST["poll_id"], $_POST["post_id"], $_POST["candidate_id"])){
     //           $idUser = intval($_POST["user_id"]);
     //           $idPoll = intval($_POST["poll_id"]);
     //           $idPost = intval($_POST["post_id"]);
     //           $idCandidate = intval($_POST["candidate_id"]);
     //           if($idUser > 0 && $idPoll > 0 && $idPost > 0 && $idCandidate > 0){
     //                return json_encode($this->controller->vote($idUser, $idPoll, $idPost, $idCandidate));
     //           }
     //           return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
     //      }else{
     //           return json_encode(["status"=>"fail", "message"=>"you must post data {idUser, idPoll, idPost, idCandidate}", "data"=>$_POST]);
     //      }
     // });

     // vote with card code/ with user-link-mode
     $this->add_roote("POST", "vote/cardmode", function (){
          // vote action with card mode activate for a poll
          if(isset($_POST, $_POST["card_code"], $_POST["poll_id"], $_POST["post_id"], $_POST["candidate_id"], $_POST["mode"])){
               $card_code = $_POST["card_code"];
               $idPoll = intval($_POST["poll_id"]);
               $idPost = intval($_POST["post_id"]);
               $idCandidate = intval($_POST["candidate_id"]);
               $mode = strtolower(trim($_POST["mode"]));
               if(strlen($card_code) > 0 && $idPoll > 0 && $idPost > 0 && $idCandidate > 0){
                    // for cardmode
                    if($mode == "cardmode") return json_encode($this->controller->voteInCardMode());

                    // for user-link-mode: try to generate a PDF receipt on success. The controller method will stream the PDF and exit on success.
                    if($mode == "user-link-cardmode"){
                         $res = $this->controller->voteWithUserLinkedCardAndPdf();
                         if(is_array($res)) return json_encode($res);
                         return json_encode(["status"=>"fail", "message"=>"Unable to generate vote receipt PDF"]);
                    }

                    return json_encode(["status"=>"fail", "message"=>"Unkwon vote mode"]);
               }
               return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
          }else{
               return json_encode(["status"=>"fail", "message"=>"you must post data {card_code, idPoll, idPost, idCandidate}", "data"=>$_POST]);
          }
     });

     //validate card code for a poll
     $this->add_roote("POST", "vote/validate/card", function(){
          try{
               if(isset($_POST, $_POST["card_code"], $_POST["poll_id"])){
                    $card_code = $_POST["card_code"];
                    $idPoll = intval($_POST["poll_id"]);
                    $mode = isset($_POST['mode']) ? strtolower(trim($_POST['mode'])) : null;
                    if(strlen($card_code) > 0 && $idPoll > 0){
                         return json_encode($this->controller->validateCardCodeForPoll($idPoll,$card_code,$mode));
                    }
                    return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
               }else{
                    return json_encode(["status"=>"fail", "message"=>"you must post data {card_code, idPoll}", "data"=>$_POST]);
               }
          }catch (Exception $e){
               return json_encode(['status'=>'fail','message'=>$e->getMessage()]);
          }
     });

     // check if a vote is in progress for a poll
     $this->add_roote("get", "poll/{idPoll}/isVoteInProgress", function($idPoll){
          $idPoll = intval($idPoll);
          if($idPoll > 0){
               return json_encode($this->controller->isVoteInProgress($idPoll));
          }
          return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
     });

     // start a vote for a poll
     $this->add_roote("POST", "poll/start", function(){
          if(isset($_POST, $_POST["idPoll"])){
               $idPoll = intval($_POST["idPoll"]);
               if($idPoll > 0){
                    return json_encode($this->controller->startVote($idPoll));
               }
               return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
          }else{
               return json_encode(["status"=>"fail", "message"=>"you must post data {idPoll}", "data"=>$_POST]);
          }
     });

     
     // stop a vote for a poll
     $this->add_roote("POST", "poll/stop", function(){
          if(isset($_POST, $_POST["idPoll"])){
               $idPoll = intval($_POST["idPoll"]);
               if($idPoll > 0){
                    return json_encode($this->controller->endVote($idPoll));
               }
               return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
          }else{
               return json_encode(["status"=>"fail", "message"=>"you must post data {idPoll}", "data"=>$_POST]);
          }
     });
   }

    /**
     * poll endPoints
     */

     private function pollEndPoint(){
          // return all the polls
          $this->add_roote("get", "polls", function():string{
               return json_encode($this->controller->getPolls());
          });

          // return polls json ordered by status
          $this->add_roote("get", "polls/ordered", function():string{
               return json_encode($this->controller->getPollsOrderedByStatus());
          });


          // return one poll
          $this->add_roote("get", "poll/{id}", function(int $id){
               return json_encode($this->controller->getPoll($id));
          });

          // add a poll and informations required are userId, title, description, date_start, date_end
          $this->add_roote("POST", "poll", function (){
               return json_encode($this->controller->addPoll());
          });

          // go in card mode, make a pool accessible thanks to a code card
          $this->add_roote("POST", "poll/card-mode/accessdemand", function(){
               header('Content-Type: application/json');
               if(isset($_POST, $_POST['id_poll'], $_POST['card_number'], $_POST['mode'])){
                    $id_poll = $_POST['id_poll'];
                    $card_number = $_POST['card_number'];
                    $mode = strtolower($_POST["mode"]);

                    // mode vefication
                    if($mode != "cardmode" && $mode != "user-link-cardmode"){
                         return json_encode([
                              "status"=>"fail",
                              "message"=>"Mode must be cardmode or user-link-mode",
                              "mode" => $mode
                         ]);
                    }

                    if(!is_numeric($id_poll) && !is_numeric($card_number)){
                         return json_encode([
                              "status"=>"fail", 
                              "message"=>"id_poll and card_number must be intergers"
                         ]);
                    }

                    return json_encode($this->controller->setToMode($id_poll, $card_number, $mode));
                    
                    // // for card-mode
                    // if($mode == "cardmode"){
                    //      // numerics params verifications
                    //      if(is_numeric($id_poll) && is_numeric($card_number)){
                    //           return json_encode($this->controller->setToCardMode(idPoll: $id_poll, cardsNumber:$card_number, mode:$mode));
                    //      }else{
                    //           return json_encode([
                    //                "status"=>"fail", 
                    //                "message"=>"id_poll and card_number must be intergers"
                    //           ]); 
                    //      }
                    // }

                    // // for user-card-linked mode
                    // if($mode == "user-link-cardmode"){
                    //      $data = $_POST; 
                    //      if(!isset($data['id_poll'], $data['card_number']) && !is_int($data['id_poll']) && !is_int($data['card_number'])) return json_encode([
                    //           'status'=>'fail', 
                    //           'message'=>'id_poll and id_user must be sent and must be numbers'
                    //      ]);
                         
                    //      return json_encode(
                    //           $this->controller->setToUserLinkedCardMode($data['id_poll'], $data['card_number'])
                    //      );
                    // }
               }else{
                    return json_encode([
                         "status"=>"fail", 
                         "message"=>"you must send the id_poll, the card_number and the mode"
                    ]);
               }
          });

          
          // $this->add_roote('POST', "poll/set-linked-card-user-mode", function(){
               
          // });
          
          //     $this->add_roote("update", "poll", function(){
          //           return json_encode(["update"=>true])
          //     });

          $this->add_roote("delete", "poll/{pollId}", function($pollId){
               if(is_numeric($pollId)){
                    return json_encode($this->controller->deletePoll((int) $pollId)); 
               }
               return json_encode([
                    "status"=>"fail",
                    "message"=>"the id must interger"
               ]);
          });
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
               header('Content-Type: application/json');
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
               header('Content-Type: application/json');
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

          $this->add_roote("GET", "candidate", function(){
               header('Content-Type: application/json');
               return json_encode($this->controller->getAllCandidate());
          });
     }

   private function postEndPoints(){
     // add post endpoint
     $this->add_roote("POST", "post/add", function (){
          header('Content-Type: application/json');
          return json_encode($this->controller->addPost());
     });

     // get avaible post for a given card-code and poll
     $this->add_roote("get", "post/getavailablepostcard/poll/{pollId}/card/{cardcode}", function($pollId, $cardcode){
          header("Content-Type:application/json");
          return json_encode($this->controller->getAvablePostForCard($pollId, $cardcode));
     });

     // allow downloading the vote receipt PDF for a poll and card (if card has completed all posts)
     $this->add_roote("get", "vote/receipt/poll/{pollId}/card/{cardcode}", function($pollId, $cardcode){
          $res = $this->controller->getVoteReceiptPdfForCard(intval($pollId), $cardcode);

          if (is_string($res)) {
               // Stream PDF
               header('Content-Type: application/pdf');
               header('Content-Disposition: inline; filename="vote_receipt_poll_' . intval($pollId) . '.pdf"');
               header('Cache-Control: no-cache, no-store, must-revalidate');
               header('Pragma: no-cache');
               header('Expires: 0');
               echo $res;
               exit;
          }

          header('Content-Type: application/json');
          return json_encode($res);
     });
   }

   private function usersEnpoints(){
     header('Content-Type: application/json');
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

     // link user to card
     $this->add_roote("POST", 'user/link-to-card', function(){
          $data = $_POST;
          if(isset($data['id_user'], $data['id_poll']) || is_int($data['id_user']) || is_int($data['id_poll'])) 
               return ['status'=>'fail', 'message'=>'you must send id_user and id_poll and they must be intergers'];

          return json_encode($this->controller->linkUserToCard($data['id_user'], $data['id_poll']));
     });

     // create users by uploading csv file
     $this->add_roote("POST", "users/create/from-csv", function(){
          header('Content-Type: application/json');
          return json_encode($this->controller->createUsersFromCsvData());
     });

   }

   private function statsEndPoints(){
     $this->add_roote("get", "statistics", function(){
          return json_encode($this->controller->getStatistics());
     });
   }

   private function resultEndPoints(){
     header('Content-Type: application/json');
     // get the results of a poll
     $this->add_roote("get", "poll/results/{idPoll}", function($idPoll){
          if(is_numeric($idPoll)){
               $idPoll = intval($idPoll);
               if($idPoll > 0){
                    return json_encode($this->controller->getResultOfPoll($idPoll));
               }
          }
          return json_encode(["status"=>"fail", "message"=>"the id must be a positive integer", "data"=>$_POST]);
     });

     $this->add_roote('get', 'poll/results/pdf/{idPoll}', function($idPoll){
          if(is_numeric($idPoll) && intval($idPoll) > 0){
               return $this->controller->getResultPollPdf($idPoll);
          }
     });
   }

   private function getImagesEndPoints(){
     // get the first image of the code_owner from the system
     // $this->add_roote("get", "api/{token_user}/image/{code_image_owner}",function($token_user, string $code_image_owner){
     //      return ($this->controller->is_token_valid($token_user))? 
     //           $this->controller->get_image($code_image_owner):
     //           $this->badTokenError($token_user);
     // });

     // // get the image code_owner from the system or a specific image by its index 
     // $this->add_roote("get", "api/{token_user}/image/{code_image_owner}/{image_index}",function($token_user, string $code_image_owner, int $image_index = 0){
     //      return ($this->controller->is_token_valid($token_user))? 
     //           $this->controller->get_image($code_image_owner, $image_index):
     //           $this->badTokenError($token_user);
     // });
     $this->add_roote("post", "user/image/upload", function() {
          header('Content-Type: application/json; charset=utf-8');

          if (!isset($_FILES['image'])) {
               http_response_code(400);
               return json_encode(['error' => 'Aucun fichier envoyé.']);
          }

          if (!isset($_POST['userid']) || !is_numeric($_POST['userid'])) {
               http_response_code(400);
               return json_encode(['error' => 'Aucun id utilisateur reçu ou id n\'est pas un entier']);
          }

          $file = $_FILES['image'];
          $userId = intval($_POST['userid']);

          // Erreurs upload PHP
          if ($file['error'] !== UPLOAD_ERR_OK) {
               http_response_code(400);
               return json_encode(['error' => 'Erreur lors de l\'upload.', 'code' => $file['error']]);
          }

          // Vérifie tmp_name et is_uploaded_file
          if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
               http_response_code(400);
               return json_encode(['error' => 'Fichier temporaire invalide.']);
          }

          // Détecte le vrai MIME côté serveur
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $file['tmp_name']);
          finfo_close($finfo);

          $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/heic'=>'heic', 'image/heif'=>'heif' ];

          if (!array_key_exists($mime, $allowed)) {
               http_response_code(400);
               return json_encode(['error' => 'Format d\'image non autorisé.', 'detected' => $mime]);
          }

          // Taille max (20MB)
          $maxBytes = 20 * 1024 * 1024;
          if ($file['size'] > $maxBytes) {
               http_response_code(413);
               return json_encode(['error' => 'Fichier trop volumineux. Limite: 20MB']);
          }

          // Appelle la méthode du controller qui gère le stockage
          try {
               $result = $this->controller->postUserImage($userId, $file, $mime);
               // postUserImage doit renvoyer un array avec success/url/filename ou lancer Exception
               http_response_code(201);
               return json_encode($result);
          } catch (\Exception $e) {
               http_response_code(500);
               return json_encode(['error' => 'Erreur serveur lors de l\'enregistrement du fichier.', 'message' => $e->getMessage()]);
          }
     });


     $this->add_roote("get", "user/image/{userId}", function($userId){
          if(is_numeric($userId)){
               return $this->controller->getUserImage($userId);
          }else{
               header('Content-Type: application/json');
               json_encode([
                    "status"=>"fail", 
                    "message"=>"id must be integer"
               ]);
          }
     });
   }
   

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
