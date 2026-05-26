require_once '../app/Repositories/ReclamationRepository.php';

class ProfessorController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new ReclamationRepository();
    }

    // reclamations professeur
    public function reclamations()
    {
        $reclamations = $this->repo->getAll();

        require '../views/pages/professor/reclamations.php';
    }

    // traiter reclamation
    public function traiter()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->repo->traiter($id);
        }

        header('Location: index.php?page=prof-reclamations');
        exit;
    }
}