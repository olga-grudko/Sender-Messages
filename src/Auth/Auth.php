<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 01.12.18
 * Time: 23:14
 */

namespace App\Auth;

/**
 * Класс для авторизации по токену
 * Class Auth
 * @package App\Auth
 */
class Auth
{
    private $tokenKey = 'dgwer3uith2gf4fgheru';
    public $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Проверяет валидность токена
     * @return bool
     */
    public function isValidToken() : bool
    {
        if($this->token == $this->tokenKey) {
            return true;
        }
        return false;
    }
}