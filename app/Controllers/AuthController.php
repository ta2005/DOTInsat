<?php

require_once BASE_PATH . '/app/Repositories/AuthRepository.php';

class AuthController
{
    private PDO $pdo;

    private AuthRepository $authRepo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        $this->authRepo = new AuthRepository($pdo);
    }

    // interface de login
    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {

            $this->redirectToHome();

        }

        $error = $_GET['error'] ?? null;

        require BASE_PATH . '/views/auth/login.php';
    }

    // ajout info login
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');

        $password = trim($_POST['password'] ?? '');

        $remember = isset($_POST['remember_me']);

        if ($email === '' || $password === '') {

            $this->redirectToLogin(
                'Email et mot de passe requis.'
            );
        }

        $user = $this->authRepo->authenticate(
            $email,
            $password
        );

        if (!$user) {

            $this->redirectToLogin(
                'Email ou mot de passe incorrect.'
            );
        }

        $this->startUserSession($user);

 
        //creation de token w cookie pour remember me
        if ($remember) {

            $token = $this->authRepo
                ->createRememberToken((int)$user['id']);

            setcookie(
                COOKIE_REMEMBER_TOKEN,
                $token,
                [
                    'expires'  => time() + REMEMBER_ME_DURATION,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );

            setcookie(
                COOKIE_REMEMBER_USER,
                (string)$user['id'],
                [
                    'expires'  => time() + REMEMBER_ME_DURATION,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }

        $this->redirectToHome();
    }


    public function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {

            $this->authRepo->clearRememberToken(
                (int)$_SESSION['user_id']
            );
        }

        $_SESSION = [];

        session_destroy();

        // Supprimer les cookies 
        foreach (
            [COOKIE_REMEMBER_TOKEN, COOKIE_REMEMBER_USER]
            as $cookie
        ) {

            setcookie(
                $cookie,
                '',
                time() - 3600,
                '/'
            );
        }

        header('Location: /?page=login');

        exit;
    }

 
    private function startUserSession(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];

        $_SESSION['user_email'] = $user['email'];

        $_SESSION['user_nom'] = $user['nom'] ?? '';

        $_SESSION['user_prenom'] = $user['prenom'] ?? '';

        $_SESSION['user_role'] = $user['role'] ?? '';

        $_SESSION['filiere'] = $user['filiere'] ?? '';

        $_SESSION['annee'] = (int)($user['annee'] ?? 0);
    }

    // Redirection vers la page de login avec message d'erreur
    private function redirectToLogin(string $error): never
    {
        header(
            'Location: /?page=login&error=' .
            urlencode($error)
        );

        exit;
    }

    // Redirection vers la page d'accueil après connexion
    private function redirectToHome(): never
    {
        header('Location: /?page=home');

        exit;
    }
}