<?php
namespace Dls\evoting\Api;

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

        // user endPont
        $this->usersEnpoints();

        // statiscs endpoint
        $this->statsEndPoints();

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
     $this->add_roote("get", "statisctis", function(){
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
