<?php

require_once 'Repository.php';

class UserRepository extends Repository
{
    // récupérer utilisateur par email
    public function findByEmail($email)
    {
        $sql = "
            SELECT *
            FROM users
            WHERE email = :email
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // récupérer tous les utilisateurs
    public function getAll()
    {
        $sql = "
            SELECT *
            FROM users
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}