<?php
namespace Controller;
include_once __DIR__."/../Class/User.php";

class UserController
{
    static private $instance = null;
    private $UserList = array();
    private $__dataBase__controller;
    public $__usersCount;

    public static function getInstance(){
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    private function __construct()
    {
        $this->__dataBase__controller = MySQLController::getInstance();
        $this->set($this->__dataBase__controller->__Admin__UserQuery());
    }
    private function set(array $sql_question){
        $this->__usersCount =  count($sql_question);
        unset($this->UserList);
        if (!is_null($sql_question))
        foreach ($sql_question as $item){
            $this->UserList[] = new User(
                $item["idUser"],
                $item["idScore"],
                $item["Email"],
                $item["Passwd"],
                $item["LastLogined"],
                $item["Type"],
                $item["Level"],
                $item["IMG"]
            );
        }else{
            echo "W domu dzialalo";
        }
    }

    public function add($login, $password, $LastLogined, $idLevel = 0, $type = 2, $idScore = 0, $IMG=""){

        $this->__dataBase__controller->regestration($login, $password, $LastLogined, $type, $idLevel, $idScore, $IMG);
        $this->set($this->__dataBase__controller->__Admin__UserQuery());
    }
    public function remove(string $email){
        $this->__dataBase__controller->__Admin__UserRemove($email);
        $this->set($this->__dataBase__controller->__Admin__UserQuery());

    }
    public function update($EmailToChange, $PasswordToChange, $IMG){
        $this->__dataBase__controller->__Admin__UserUpdate($EmailToChange, $PasswordToChange, $IMG);
        $this->set($this->__dataBase__controller->__Admin__UserQuery());
    }
    public function updateUserLoginDate($login, $LastLogined){
        $this->__dataBase__controller->__User__UpdateLastLogined($login, $LastLogined);
        $this->set($this->__dataBase__controller->__Admin__UserQuery());
    }
    public function nextLevel($email){
        $this->__dataBase__controller->__User__UserNextLevel($email);
    }
    public function returnArray(){
        return $this->UserList;
    }
    public function SearchByEmail($User_Email){
        foreach($this->UserList as $value){
            if ($value->getEmail() == $User_Email) return $value;
        }
        return null;
    }
}