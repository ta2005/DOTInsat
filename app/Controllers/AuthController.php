<?php

class AuthController
{
    // afficher login
    public function login()
    {
        require '../views/auth/login.php';
    }

    // connexion utilisateur
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // exemple simple
            if ($email === 'admin@test.com' && $password === '1234') {

                session_start();

                $_SESSION['user'] = $email;

                header('Location: index.php?page=home');
                exit;
            }

            header('Location: index.php?page=login');
            exit;
        }
    }

    // logout
    public function logout()
    {
        session_start();

        session_destroy();

        header('Location: index.php?page=login');
        exit;
    }
}