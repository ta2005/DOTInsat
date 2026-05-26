<?php
declare(strict_types=1);

class ErrorController
{
    public function __construct(private ?PDO $pdo) {}



    public function notFound(): void
    {
        $this->render(404, 'Page introuvable', "La page que tu cherches n'existe pas.");
    }

    public function methodNotAllowed(): void
    {
        $this->render(405, 'Méthode non autorisée', "Cette action n'est pas autorisée avec cette méthode HTTP.");
    }

    public function serverError(): void
    {
        $this->render(500, 'Erreur serveur', "Une erreur interne s'est produite. Vérifie les logs.");
    }

  

    private function render(int $code, string $title, string $message): void
    {
        extract(compact('code', 'title', 'message'));
        $viewPath = BASE_PATH . '/views/pages/error.php';
        include BASE_PATH . '/views/layouts/home.php';
    }
}