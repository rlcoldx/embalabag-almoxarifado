<?php

namespace Agencia\Close\Services\Login;

class LoginSession
{
    public function loginUser(array $login)
    {
        $_SESSION = [
            BASE.'user_id' => $login['id'],
            BASE.'user_nome' => $login['nome'],
            BASE.'user_email' => $login['email'],
        ];
    }

    public function userIsLogged(): bool
    {
        if (isset($_SESSION[BASE.'user_id'])){
            return true;
        }
        return false;
    }
}