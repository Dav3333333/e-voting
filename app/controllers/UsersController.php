<?php 
namespace Dls\Evoting\controllers;

use Dls\Evoting\models\Card;
use Exception;
use Datetime;

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;
use Dls\Evoting\models\User;
use Dls\Evoting\models\Poll;

class UsersController extends ControllersParent{

    private CardController $cardController;

    public function __construct(){
        parent::__construct(); 
        $this->cardController = new CardController();
    }

    public function getAll():array{
        $users =[];

        $q = $this->database->query("SELECT * FROM users");
        foreach ($q->fetchAll() as $key => $user) {
            $users[] = new User(
                id:$user["id"],
                name:$user["name"],
                email: $user["email"],
                matricule: $user["matricule"],
                RFID: $user["rfid"],
                has_image: $user["has_image"],
                imageName: $user["image_name"],
                isAdmin: $user["is_admin"],
                status: $user["status"],
            );
        }
        return $users; 
    }

    public function getUserAvaibleCandidatePoll(Poll $poll){
        $users = [];
        $q = $this->database->prepare("SELECT * FROM `users` WHERE  `users`.`id` NOT IN (SELECT `candidate`.`user_id` FROM `candidate` WHERE `candidate`.`poll_id` = ? AND `candidate`.`status` != -1 )");
        $q->execute(array($poll->getId()));
        
        while ($user = $q->fetch()) {
            $users[] = new User(
                id:$user["id"],
                name:$user["name"],
                email: $user["email"],
                matricule: $user["matricule"],
                RFID: $user["rfid"],
                has_image: $user["has_image"],
                imageName: $user["image_name"],
                isAdmin: $user["is_admin"],
                status: $user["status"],
            );
        }
        return $users;
    }

    public function getActiveUsers():array{
        $users =[];

        foreach ($this->getAll() as $key => $value) {
            if($value->getStatus() == "active"){
                $users[] = $value;
            }
        }

        return $users;
    }

    public function getUserById(int $id):User|bool{
        foreach ($this->getAll() as $key => $user) {
            if($user->getId()== $id){
                return $user;
            }
        }
        return false;
    }

    /**
     * return the user that his mail is mail and matricule
     * @param string $matricule
     * @param string $email
     * @return \Dls\Evoting\models\User|bool
     */
    public function getUserByMailMatricule(string $matricule, String $email):User|bool{
        foreach ($this->getAll() as $key => $user) {
            if($user->getMatricule() == $matricule && $user->getEmail() == $email) {
                return $user;
            }
        }
        return false;
    }

    public function createUser(string $matricule, string $email, string $name, string $rfid):User|Bool|array{  
        if (!$this->isUserMailExist($email, $matricule)) {
            $status = "active";
            $is_admin = 0;
            $q = $this->database->prepare("INSERT INTO users(name, matricule, email, rfid, is_admin, status) 
                                                            VALUES(?, ?, ?, ?, ?, ?)");
            $rep =  $q->execute(array($name, $matricule, $email, $rfid, $is_admin, $status));

            return ($rep)? $this->getUserByMailMatricule($matricule,$email ):false;
        }
        return ["Identity used"];
    }

    public function deleteUser(User $user):bool{
        if($this->isUserExist(user: $user)) {
            $q = $this->database->prepare("DELETE FROM users WHERE id = ? ");
            return $q->execute(array($user->getId()));
        }
        return false; 
    }

    public function updateUser(User $user):bool{
        return true; 
    }


    /**
     * return true if the user exist
     * @param \Dls\Evoting\models\User $user
     * @return bool
     */
    public function isUserExist(User $user):bool{
        return in_array($user, $this->getAll());
    }

    public function isUserMailExist(string $email, string $matricule):bool{
        foreach ($this->getAll() as $key => $value) {
            if ($value->getMatricule() == $matricule || $value->getEmail() == $email) {
                return true;
            }
        }
        return false; 
    }

    public function isAdmin(User $user):bool{
        return $user->isAdmin();
    }

    // enrolements codes

    /**
     * return true if the user is enroled and false if not
     * @param User $user
     * @param Poll $poll
     * @throws Exception
     * @return bool
     */
    private function isUserEnroledToPoll(User $user, Poll $poll):?bool{
        try {
            $q = $this->database->query("SELECT * FROM enrolements WHERE id_poll = ".$poll->getId()
                                    ." AND id_user = ".$user->getId()); 
            return $q->rowCount() == 1;
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * return true if the enrolement process is done and false if not
     * @param User $user
     * @param Poll $poll
     * @throws Exception
     * @return bool
     */
    private function enroleUserToPoll(User $user, Poll $poll):bool|string|null|array{
        try {
            if($this->isUserEnroledToPoll($user, $poll)) return null; 

            // getting the card
            $card = $this->cardController->linkUserToCard($user, $poll);

            if(!$card instanceof Card){
                return [$user, $poll, $card];
            }
            $q = $this->database->prepare("INSERT INTO enrolements(id_poll, id_user ,has_card, card_code, expired, date_time) VALUES(?,?,?,?,?, NOW())");
            return $q->execute([$poll->getId(), $user->getId(), true, $card->get_code_card(),false]);
            // return false;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


    /**
     * return the array of users enroled to a param poll passed
     * @param Poll $poll the Poll object
     * @throws Exception
     * @return array return a array of users enroled to a poll passed as param
     */
    public function getEnroledUsersOfPoll(Poll $poll):array{
        try {
            $enroled = []; 
            foreach ($this->getAll() as $key => $value) {
                if($this->isUserEnroledToPoll($value, $poll)) $enroled[] = $value; 
                continue;
            }
            return $enroled;
        } catch (\Throwable $th) {
            throw new Exception("Error Processing Request", 1);
        }
    }

    /**
     * return the statistitcs of the users
     * @return void
     */
    public function getStats():array{
        return [
            "users"=>count($this->getAll()), 
            "activeUsers"=>count($this->getActiveUsers()), 
            "inactiveusers"=>count($this->getAll()) - count($this->getActiveUsers()),
        ];
    }

    /**
     * return a user passed or default image if the user doesnt have a image stored
     * @param User $user
     * @return never
     */
    public function getUserImage(User $user){
        // read file to send
        $path = "images/users/";
        
        if($user->getImageName() == null){
            $path .= basename("default-image.png");
        }else{
            $path .= basename($user->getImageName());
        }
        
        if(!file_exists($path)) $path =   "images/users/".basename("default-image.png");
        
        $mime = mime_content_type($path);
        // defining headers
        header('Content-Type: '.$mime);
        // header('Content-Disposition: inline; filename="image.png"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header("Content-Length:".filesize($path));
        header('Expires: 0');

        readfile($path);
        exit;
    }

    /**
     * return the user image file
     * @param User $user
     * @param mixed $file
     * @return bool
     */
    public function uploadUserImage(User $user, $file): bool{
        $uploadDir = __DIR__ . "/../../app/images/users/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        // Vérifie MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);
        $allowed = ["image/jpeg", "image/png", "image/gif"];
        if (!in_array($mime, $allowed)) return false;

        // Nom unique
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = substr(md5($user->getEmail()), 0, 8) . "_" . uniqid("img_") . "." . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file["tmp_name"], $destination)) {
            $q = $this->database->prepare("UPDATE users SET has_image = ?, image_name = ? WHERE id = ?");
            return $q->execute([1, $filename, $user->getId()]);
        }

        error_log("Upload failed for {$file['name']} → {$destination}");
        return false;
    }

    private function rankUserByPoll($arrayData):?array{
        try {
            $polls =  array_values(array_unique(array_column($arrayData, "poll")));
            $data_filetered = []; 

            foreach ($arrayData as $key => $value) {
                if(!in_array($value['poll'] , $polls))continue;
                $data_filetered[$value['poll']][] = $value;
            }
            return $data_filetered; 
        } catch (\Throwable $th) {
            throw new Exception("Error Processing Request", 1);
        }
    }

    public function getPollFromTitleText($title):Poll|array|null{
        try {
            $q = $this->database->prepare("SELECT * FROM poll WHERE title LIKE ? LIMIT 1");
            $q->execute(array($title."%"));
    
            if($q->rowCount() !== 1) return null;
    
            $q = $q->fetch($this->database::FETCH_ASSOC);
            return new Poll(
                    id:$q['id'], 
                    title:$q['title'],
                    date_start: new DateTime(datetime:$q['date_start']),
                    date_end: new DateTime(datetime:$q['date_end']), 
                    status:$q['status'], 
                    description:$q['description'], 
                    in_card_mode:($q['in_card_mode']), 
                    card_user_link_mode : ($q['card_user_link_mode'])
                );
        } catch (\Throwable $th) {
            return ['error'=>$th->getMessage()];
        }
    }

    /**
     * create new Users from the csv file uplaodes
     * @param mixed $handle
     * @return array{message: string, status: string}
     */
    public function createUsersFromCvsFile($handle):array{
        try {
            $header = fgetcsv($handle);
            $cardController = new CardController();

            if ($header === false) return ['status'=>'fail', 'message'=>'le fichier ne contient aucune donnes'];

            // Normalise et détecte si le fichier contient un en-tête valide
            $header = array_map(function($h){ return strtolower(trim((string)$h)); }, $header);
            $expectedKeys = ['name','matricule','email','rfid','poll'];

            // Si l'en-tête ne contient pas les colonnes attendues, on considère qu'il n'y a pas d'en-tête
            if (count(array_intersect($expectedKeys, $header)) < count($expectedKeys)) {
                // remettre le pointeur au début pour lire toutes les lignes (y compris la première)
                rewind($handle);
                $header = $expectedKeys;
            }

            $data = [];
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === null) continue;
                // Ignore les lignes vides
                $allEmpty = true;
                foreach ($row as $cell) { if (trim((string)$cell) !== '') { $allEmpty = false; break; } }
                if ($allEmpty) continue;

                if (count($row) !== count($header)) {
                    continue;
                }
                $combined = @array_combine($header, $row);
                if ($combined !== false) {
                    $data[] = $combined;
                }
            }

            if (empty($data)) return ['status'=>'fail', 'message'=>'le fichier est vide'];

            // Vérifie que les bonnes colonnes sont présentes (après normalisation)
            $firstRowKeys = array_map('strtolower', array_keys($data[0]));
            $missing = array_diff($expectedKeys, $firstRowKeys);
            if (!empty($missing)) {
                return ['status'=>'fail', 'message'=>'le fichier doit contenir les colonnes: name, matricule, email, rfid, poll'];
            }

            $data = $this->rankUserByPoll($data);

            $created_user = [];
            $enroled_user = [];
            $founded_poll = [];
            $unfounded_poll = [];
            $cardsEnroled = [];

            // adding users using create user function
            foreach ($data as $key => $pollData) {
                $poll = $this->getPollFromTitleText($key);

                if ($poll == null) {
                    $unfounded_poll[] = $key;
                    continue;
                }

                // Ensure there are enough cards for this poll: generate missing ones if needed
                $needed = count($pollData) - $this->cardController->countCardOfPoll($poll);
                if ($needed > 0) {
                    $this->cardController->generateCardForPoll($poll, $needed);
                }

                foreach ($pollData as $k => $value) {
                    // check if the user doesn't exist
                    if (!$this->isUserMailExist($value['email'], $value['matricule'])) {
                        $created_user[] = $this->createUser($value['matricule'], $value['email'], $value['name'], $value['rfid']);
                    }

                    $user = $this->getUserByMailMatricule($value['matricule'], $value['email']);
                    if (!($user instanceof User)) {
                        continue;
                    }

                    $enrollRes = $this->enroleUserToPoll($user, $poll);
                    $cardsEnroled[] = $enrollRes;
                    if ($enrollRes === true) {
                        $enroled_user[] = $user;
                        $founded_poll[] = $poll;
                    }
                }
            }
            return [
                'status'=> 'success', 
                'message'=>'ajout reussi', 
                'created_user'=>$created_user, 
                'enroled_user'=>$enroled_user, 
                'founded_poll'=>$founded_poll,
                'unfounded_poll'=>$unfounded_poll,
                'data'=>$data,
                'cards_enroled'=> $cardsEnroled
            ];
        } catch (\Throwable $th) {
            return array($th);
        }
    }

    public function uploadUsersCsvFile($file):bool{
        $uploadDir = __DIR__ . "/../../app/files/uploadUsersFile/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        // Vérifie MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);
        $allowed = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/octet-stream'];
        if (!in_array($mime, $allowed)) return false;

        // Nom unique
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = substr(md5(time()),  0, 10) . "_" . uniqid("file__") . "." . $ext;
        $destination = $uploadDir . $filename;

        return move_uploaded_file($file["tmp_name"], $destination); 
    }

}