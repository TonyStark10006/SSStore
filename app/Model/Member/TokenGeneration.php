<?php

namespace App\Model\Member;


class TokenGeneration
{
    private $token;
    private $randomMsg;

    public function __construct()
    {
        $this->generateToken();
    }

    protected function generateToken() : void
    {
        $salt = $this->generateRanMsg();
        $this->token = substr(password_hash($salt, PASSWORD_DEFAULT, ['cost' => 12]), 9);
    }

    protected function generateRanMsg() : string
    {
        return $this->randomMsg = mt_rand(1, 99999999) . date('HisYMd');
    }


    public function getToken() : string
    {
        return $this->token;
    }

    public function getRandomMsg() : string
    {
        return $this->randomMsg;
    }
}