<?php

require_once BASE_PATH . '/app/Repositories/UserRepository.php';

class AuthRepository
{
    private UserRepository $userRepo;

    public function __construct(PDO $db)
    {
        $this->userRepo = new UserRepository($db);
    }
    // Authentifie un utilisateur par email et mot de passe
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return null;
        }

        
        if (password_verify($password, $user['mot_passe'])) {
            return $user;
        }

        // 
        if ($user['mot_passe'] === $password) {
            return $user;
        }

        return null;
    }

   //cree token remember me
    public function createRememberToken(int $userId): string
    {
        $token       = bin2hex(random_bytes(32));  
        $hashedToken = hash('sha256', $token);

        $this->userRepo->saveRememberToken($userId, $hashedToken);

        return $token;
    }

    // Récupère un utilisateur par son token remember me

    public function getUserByRememberToken(string $rawToken): ?array
    {
        $hashedToken = hash('sha256', $rawToken);
        return $this->userRepo->findByRememberToken($hashedToken);
    }

    // Efface le remember token
    public function clearRememberToken(int $userId): void
    {
        $this->userRepo->clearRememberToken($userId);
    }
}
