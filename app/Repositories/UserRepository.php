<?php

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT
                u.id,
                u.cin,
                u.nom,
                u.prenom,
                u.email,
                u.mot_passe,

                CASE
                    WHEN a.id IS NOT NULL THEN 'admin'
                    WHEN p.id IS NOT NULL THEN 'professeur'
                    WHEN e.id IS NOT NULL THEN 'etudiant'
                    ELSE 'inconnu'
                END AS role,

                UPPER(
                    TRIM(
                        (e.niveau_scolaire_info).classe
                    )
                ) AS filiere,

                (e.niveau_scolaire_info).annee AS annee

            FROM users u

            LEFT JOIN admin a
                ON a.id = u.id

            LEFT JOIN professeur p
                ON p.id = u.id

            LEFT JOIN etudiant e
                ON e.id = u.id

            WHERE u.email = :email

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                u.id,
                u.cin,
                u.nom,
                u.prenom,
                u.email,

                CASE
                    WHEN a.id IS NOT NULL THEN 'admin'
                    WHEN p.id IS NOT NULL THEN 'professeur'
                    WHEN e.id IS NOT NULL THEN 'etudiant'
                    ELSE 'inconnu'
                END AS role,

                UPPER(
                    TRIM(
                        (e.niveau_scolaire_info).classe
                    )
                ) AS filiere,

                (e.niveau_scolaire_info).annee AS annee

            FROM users u

            LEFT JOIN admin a
                ON a.id = u.id

            LEFT JOIN professeur p
                ON p.id = u.id

            LEFT JOIN etudiant e
                ON e.id = u.id

            WHERE u.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function saveRememberToken(
        int $userId,
        string $hashedToken
    ): void {

        try {

            $stmt = $this->db->prepare("
                UPDATE users
                SET remember_token = :token
                WHERE id = :id
            ");

            $stmt->execute([
                ':token' => $hashedToken,
                ':id'    => $userId
            ]);

        } catch (PDOException $e) {

            error_log($e->getMessage());
        }
    }

    public function findByRememberToken(
        string $hashedToken
    ): ?array {

        try {

            $stmt = $this->db->prepare("
                SELECT id
                FROM users
                WHERE remember_token = :token
                LIMIT 1
            ");

            $stmt->execute([
                ':token' => $hashedToken
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->findById(
                (int) $row['id']
            );

        } catch (PDOException $e) {

            error_log($e->getMessage());

            return null;
        }
    }


    public function clearRememberToken(
        int $userId
    ): void {

        try {

            $stmt = $this->db->prepare("
                UPDATE users
                SET remember_token = NULL
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $userId
            ]);

        } catch (PDOException $e) {

            error_log($e->getMessage());
        }
    }
}