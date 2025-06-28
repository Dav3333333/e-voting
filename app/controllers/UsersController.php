<?php 
NAMESPACE Dls\Evoting\controllers;

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;
use Dls\Evoting\models\User;

class UsersController extends ControllersParent{

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

}