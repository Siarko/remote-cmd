<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2019
 * Time: 10:55
 */

namespace P3rc1val\auth;


use P3rc1val\database\models\Users;
use P3rc1val\routing\Router;

class User {

    const LOGIN_ERR_INVALID_DATA = 0;
    const LOGIN_ERR_NO_DATA = 1;

    const LOGIN_ERR = [
        User::LOGIN_ERR_INVALID_DATA => "Nieprawidłowe dane logowania",
        User::LOGIN_ERR_NO_DATA => "Nie wprowadzono danych"
    ];

    public $login;
    public $nick;

    public static function getLoginErrCause($causeId){
        if(array_key_exists($causeId, User::LOGIN_ERR)){
            return User::LOGIN_ERR[$causeId];
        }
        return null;
    }

    /**
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function authenticate(){
        if(!$this->isLogged()){
            Router::resolver(Router::REDIRECT)->resolve('login')->end();
        }
    }

    public function login($login = null, $password = null){
        if($_SESSION != null and array_key_exists('login', $_SESSION) and $_SESSION['login'] != null){
            /* @var $user Users*/
            $user = Users::find($_SESSION['login'])->one();
            $this->setData($user);
        }else{
            /* @var $user Users*/
            $user = Users::find([
                Users::$LOGIN => $login,
                Users::$PASSWORD => $password
            ])->one();

            if($user == null){
                $this->login = null;
            }else{
                $this->setData($user);

                $_SESSION['login'] = $this->login;
            }
        }
    }

    private function setData(Users $data){
        $this->login = $data->login;
        $this->nick = $data->nick;
    }

    public function isLogged(){
        return ($this->login != null);
    }

    public function logout(){
        $_SESSION['login'] = null;
    }
}