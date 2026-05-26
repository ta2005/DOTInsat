<?php

require_once 'UserRepository.php';

class AuthRepository
{
    private $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    // login
    public function login($email, $password)
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return false;
        }

        // simple comparaison
        // plus tard utiliser password_verify()

        if ($user['password'] !== $password) {
            return false;
        }

        return $user;
    }
}