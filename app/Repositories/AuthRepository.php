<?php

require_once BASE_PATH . '/app/Repositories/UserRepository.php';

class AuthRepository
{
    private UserRepository $userRepo;

    public function __construct(PDO $db)
    {
        $this->userRepo = new UserRepository($db);
    }

    /**
     * Vérifie les identifiants et retourne l'utilisateur ou null.
     * Utilise password_verify() (bcrypt).
     * Fallback texte brut pour la période de migration.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return null;
        }

        // Vérification bcrypt (production)
        if (password_verify($password, $user['mot_passe'])) {
            return $user;
        }

        // Fallback comparaison directe (développement / données sans hash)
        if ($user['mot_passe'] === $password) {
            return $user;
        }

        return null;
    }

    /**
     * Génère et sauvegarde un token "remember me".
     * Retourne le token brut (à placer dans le cookie).
     */
    public function createRememberToken(int $userId): string
    {
        $token       = bin2hex(random_bytes(32));  // 64 chars, aléatoire
        $hashedToken = hash('sha256', $token);

        $this->userRepo->saveRememberToken($userId, $hashedToken);

        return $token;
    }

    /**
     * Vérifie un token de cookie et retourne l'utilisateur associé.
     */
    public function getUserByRememberToken(string $rawToken): ?array
    {
        $hashedToken = hash('sha256', $rawToken);
        return $this->userRepo->findByRememberToken($hashedToken);
    }

    /**
     * Efface le remember token (logout).
     */
    public function clearRememberToken(int $userId): void
    {
        $this->userRepo->clearRememberToken($userId);
    }
}
