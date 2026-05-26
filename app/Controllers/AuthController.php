<?php

require_once BASE_PATH . '/app/Repositories/AuthRepository.php';

class AuthController
{
    private PDO            $pdo;
    private AuthRepository $authRepo;

    public function __construct(PDO $pdo)
    {
        $this->pdo      = $pdo;
        $this->authRepo = new AuthRepository($pdo);
    }

    // -------------------------------------------------------------------------
    // GET /?page=login  →  affiche le formulaire
    // -------------------------------------------------------------------------
    public function showLogin(): void
    {
        // Si déjà connecté → rediriger directement vers home
        if (!empty($_SESSION['user_id'])) {
            $this->redirectToHome($_SESSION['user_role'] ?? '');
        }

        $error = $_GET['error'] ?? null;
        require BASE_PATH . '/views/auth/login.php';
    }

    // -------------------------------------------------------------------------
    // POST /?page=do-login  →  traitement du formulaire
    // -------------------------------------------------------------------------
    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']);

        if ($email === '' || $password === '') {
            $this->redirectToLogin('Email et mot de passe requis.');
        }

        $user = $this->authRepo->authenticate($email, $password);

        if (!$user) {
            $this->redirectToLogin('Email ou mot de passe incorrect.');
        }

        // ── Ouvrir la session ──────────────────────────────────────────────
        $this->startUserSession($user);

        // ── Cookie "Se souvenir de moi" ────────────────────────────────────
        if ($remember) {
            $token = $this->authRepo->createRememberToken((int)$user['id']);
            setcookie(
                COOKIE_REMEMBER_TOKEN,
                $token,
                [
                    'expires'  => time() + REMEMBER_ME_DURATION,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                    // 'secure' => true,  // activer en HTTPS
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

        $this->redirectToHome($user['role']);
    }

    // -------------------------------------------------------------------------
    // GET|POST /?page=logout  →  déconnexion
    // -------------------------------------------------------------------------
    public function logout(): void
    {
        // Effacer le remember token en DB
        if (!empty($_SESSION['user_id'])) {
            $this->authRepo->clearRememberToken((int)$_SESSION['user_id']);
        }

        // Détruire la session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();

        // Effacer les cookies remember me
        foreach ([COOKIE_REMEMBER_TOKEN, COOKIE_REMEMBER_USER] as $name) {
            setcookie($name, '', time() - 3600, '/');
        }

        header('Location: /?page=login');
        exit;
    }

    // =========================================================================
    // Helpers privés
    // =========================================================================

    /**
     * Remplit $_SESSION avec les données de l'utilisateur connecté.
     */
    private function startUserSession(array $user): void
    {
        session_regenerate_id(true); // protection fixation de session

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom']   = $user['nom']    ?? '';
        $_SESSION['user_prenom']= $user['prenom'] ?? '';
        $_SESSION['user_role']  = $user['role']   ?? 'inconnu';
    }

    /**
     * Redirige vers la page login avec un message d'erreur.
     */
    private function redirectToLogin(string $error): never
    {
        header('Location: /?page=login&error=' . urlencode($error));
        exit;
    }

    /**
     * Redirige vers la page home après connexion.
     */
    private function redirectToHome(string $role): never
    {
        header('Location: /?page=home');
        exit;
    }
}
