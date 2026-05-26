-- =====================================================
-- RESET
-- =====================================================

DROP TABLE IF EXISTS notification CASCADE;
DROP TABLE IF EXISTS vote CASCADE;
DROP TABLE IF EXISTS commentaire CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS membre_groupe CASCADE;
DROP TABLE IF EXISTS groupe CASCADE;
DROP TABLE IF EXISTS demande CASCADE;
DROP TABLE IF EXISTS reclamation CASCADE;
DROP TABLE IF EXISTS controle CASCADE;
DROP TABLE IF EXISTS enseignement CASCADE;
DROP TABLE IF EXISTS matieres CASCADE;

DROP TABLE IF EXISTS etudiant CASCADE;
DROP TABLE IF EXISTS professeur CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS users CASCADE;

DROP TYPE IF EXISTS type_demande CASCADE;
DROP TYPE IF EXISTS type_controle CASCADE;
DROP TYPE IF EXISTS statut_note CASCADE;
DROP TYPE IF EXISTS statut_requete CASCADE;
DROP TYPE IF EXISTS type_vote CASCADE;
DROP TYPE IF EXISTS format CASCADE;
DROP TYPE IF EXISTS niveau_scolaire CASCADE;

-- =====================================================
-- ENUMS
-- =====================================================

CREATE TYPE type_demande AS ENUM (
    'ATTESTATION_DE_INSCRIPTION',
    'ATTESTATION_DE_PRESENCE',
    'FEUILLES_DE_STAGE',
    'FEUILLES_DE_NOTES',
    'AUTRES'
);

CREATE TYPE type_controle AS ENUM (
    'DS',
    'EXAM',
    'TP'
);

CREATE TYPE statut_note AS ENUM (
    'EN_ATTENTE',
    'CORRIGE',
    'VERIFIE',
    'CONTESTE'
);

CREATE TYPE statut_requete AS ENUM (
    'EN_ATTENTE',
    'ACCEPTEE',
    'REFUSEE'
);

CREATE TYPE type_vote AS ENUM (
    'UPVOTE',
    'DOWNVOTE'
);

CREATE TYPE format AS ENUM (
    'QCM',
    'MIX',
    'NON_QCM'
);

CREATE TYPE niveau_scolaire AS (
    classe VARCHAR(50),
    annee INT,
    niveau VARCHAR(50),
    filiere VARCHAR(100)
);

-- =====================================================
-- USERS
-- =====================================================

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    cin INT UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_passe VARCHAR(255) NOT NULL
);

CREATE TABLE admin (
    id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    titre VARCHAR(100)
);

CREATE TABLE professeur (
    id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE etudiant (
    id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    niveau_scolaire_info niveau_scolaire
);

-- =====================================================
-- MATIERES
-- =====================================================

CREATE TABLE matieres (

    id SERIAL PRIMARY KEY,

    filiere VARCHAR(10) NOT NULL,

    niveau INT NOT NULL,

    semestre INT NOT NULL CHECK (semestre IN (1,2)),

    nom_matiere VARCHAR(255) NOT NULL,

    coefficient NUMERIC(4,2) NOT NULL CHECK (coefficient > 0),

    types_controle type_controle[] NOT NULL,

    UNIQUE (
        filiere,
        niveau,
        semestre,
        nom_matiere
    )
);

-- =====================================================
-- ENSEIGNEMENT
-- =====================================================

CREATE TABLE enseignement (

    id SERIAL PRIMARY KEY,

    nom VARCHAR(255) NOT NULL,

    date_debut TIMESTAMP,

    niveau_scolaire_info niveau_scolaire,

    professeur_id INT REFERENCES professeur(id),

    matiere_id INT REFERENCES matieres(id)
);

-- =====================================================
-- CONTROLE
-- =====================================================

CREATE TABLE controle (

    id SERIAL PRIMARY KEY,

    note NUMERIC(5,2) CHECK (note >= 0 AND note <= 20),

    type type_controle NOT NULL,

    statut statut_note NOT NULL,

    format format,

    enseignement_id INT REFERENCES enseignement(id),

    etudiant_id INT REFERENCES etudiant(id)
);

-- =====================================================
-- RECLAMATION
-- =====================================================

CREATE TABLE reclamation (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    type_controle type_controle,

    statut statut_requete,

    enseignement_id INT REFERENCES enseignement(id),

    etudiant_id INT REFERENCES etudiant(id),

    admin_id INT REFERENCES admin(id)
);

-- =====================================================
-- DEMANDE
-- =====================================================

CREATE TABLE demande (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    type type_demande,

    statut statut_requete,

    user_id INT REFERENCES users(id),

    admin_id INT REFERENCES admin(id)
);

-- =====================================================
-- GROUPE
-- =====================================================

CREATE TABLE groupe (

    id SERIAL PRIMARY KEY,

    nom VARCHAR(255) NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    moderateur_id INT REFERENCES users(id)
);

CREATE TABLE membre_groupe (

    id SERIAL PRIMARY KEY,

    date_adhesion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    user_id INT REFERENCES users(id),

    groupe_id INT REFERENCES groupe(id),

    UNIQUE(user_id, groupe_id)
);

-- =====================================================
-- POST
-- =====================================================

CREATE TABLE post (

    id SERIAL PRIMARY KEY,

    contenu TEXT NOT NULL,

    date_de_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    groupe_id INT REFERENCES groupe(id),

    auteur_id INT REFERENCES users(id)
);

CREATE TABLE commentaire (

    id SERIAL PRIMARY KEY,

    contenu TEXT NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    post_id INT REFERENCES post(id) ON DELETE CASCADE,

    auteur_id INT REFERENCES users(id)
);

CREATE TABLE vote (

    id SERIAL PRIMARY KEY,

    type type_vote NOT NULL,

    post_id INT REFERENCES post(id) ON DELETE CASCADE,

    user_id INT REFERENCES users(id),

    UNIQUE(post_id, user_id)
);

-- =====================================================
-- NOTIFICATION
-- =====================================================

CREATE TABLE notification (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    lue BOOLEAN DEFAULT FALSE,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    user_id INT REFERENCES users(id),

    createur_id INT REFERENCES users(id)
);

-- =====================================================
-- INSERT USERS
-- =====================================================

INSERT INTO users (
    cin,
    nom,
    prenom,
    email,
    mot_passe
)
VALUES

(
10000001,
'Mansouri',
'Khalil',
'admin@dotinsat.tn',
'$2y$10$adminhashed'
),

(
20000001,
'Ben Ali',
'Ahmed',
'prof1@dotinsat.tn',
'$2y$10$profhashed'
),

(
20000002,
'Trabelsi',
'Sonia',
'prof2@dotinsat.tn',
'$2y$10$profhashed'
),

(
30000001,
'Graja',
'Houssem',
'etud1@dotinsat.tn',
'$2y$10$etudhashed'
);

-- =====================================================
-- INSERT ROLES
-- =====================================================

INSERT INTO admin (id, titre)
VALUES
(1, 'Responsable pédagogique');

INSERT INTO professeur (id)
VALUES
(2),
(3);

INSERT INTO etudiant (
    id,
    niveau_scolaire_info
)
VALUES
(
4,
ROW(
    'GL',
    2,
    'Licence',
    'Génie Logiciel'
)::niveau_scolaire
);

-- =====================================================
-- INSERT MATIERES GL2 S1
-- =====================================================

INSERT INTO matieres (

    filiere,
    niveau,
    semestre,
    nom_matiere,
    coefficient,
    types_controle

)
VALUES

(
'GL',
2,
1,
'Probabilités et Statistiques',
3.00,
ARRAY['DS','EXAM']::type_controle[]
),

(
'GL',
2,
1,
'Bases de données relationnelles',
4.00,
ARRAY['DS','EXAM','TP']::type_controle[]
),

(
'GL',
2,
1,
'Atelier Python Avancé',
2.00,
ARRAY['TP']::type_controle[]
),

(
'GL',
2,
1,
'Atelier C++',
2.00,
ARRAY['TP']::type_controle[]
),

(
'GL',
2,
1,
'Systèmes d''exploitation',
3.00,
ARRAY['DS','EXAM']::type_controle[]
),

(
'GL',
2,
1,
'Programmation orientée objet',
4.00,
ARRAY['DS','EXAM','TP']::type_controle[]
),

(
'GL',
2,
1,
'Anglais',
1.50,
ARRAY['EXAM']::type_controle[]
);

-- =====================================================
-- INSERT ENSEIGNEMENTS
-- =====================================================

INSERT INTO enseignement (

    nom,
    date_debut,
    niveau_scolaire_info,
    professeur_id,
    matiere_id

)
VALUES

(
'Bases de données relationnelles',
'2026-09-01 08:00',

ROW(
    'GL',
    2,
    'Licence',
    'Génie Logiciel'
)::niveau_scolaire,

2,

2
),

(
'Programmation orientée objet',
'2026-09-01 08:00',

ROW(
    'GL',
    2,
    'Licence',
    'Génie Logiciel'
)::niveau_scolaire,

3,

6
);

-- =====================================================
-- INSERT CONTROLES
-- =====================================================

INSERT INTO controle (

    note,
    type,
    statut,
    format,
    enseignement_id,
    etudiant_id

)
VALUES

(
15.00,
'DS',
'CORRIGE',
'QCM',
1,
4
),

(
14.00,
'EXAM',
'VERIFIE',
'MIX',
1,
4
),

(
17.00,
'TP',
'CORRIGE',
'NON_QCM',
1,
4
);

-- =====================================================
-- INSERT DEMANDES
-- =====================================================

INSERT INTO demande (

    message,
    type,
    statut,
    user_id,
    admin_id

)
VALUES

(
'Besoin attestation inscription',
'ATTESTATION_DE_INSCRIPTION',
'ACCEPTEE',
4,
1
);

-- =====================================================
-- INSERT RECLAMATION
-- =====================================================

INSERT INTO reclamation (

    message,
    type_controle,
    statut,
    enseignement_id,
    etudiant_id,
    admin_id

)
VALUES

(
'Erreur de note',
'EXAM',
'EN_ATTENTE',
1,
4,
1
);

-- =====================================================
-- INSERT GROUPES
-- =====================================================

INSERT INTO groupe (
    nom,
    moderateur_id
)
VALUES

(
'GL2 Java',
4
);

INSERT INTO membre_groupe (
    user_id,
    groupe_id
)
VALUES

(
4,
1
);

-- =====================================================
-- INSERT POSTS
-- =====================================================

INSERT INTO post (
    contenu,
    groupe_id,
    auteur_id
)
VALUES

(
'Bonjour tout le monde',
1,
4
);

INSERT INTO commentaire (
    contenu,
    post_id,
    auteur_id
)
VALUES

(
'Daccord',
1,
1
);

INSERT INTO vote (
    type,
    post_id,
    user_id
)
VALUES

(
'UPVOTE',
1,
1
);

-- =====================================================
-- INSERT NOTIFICATION
-- =====================================================

INSERT INTO notification (

    message,
    lue,
    user_id,
    createur_id

)
VALUES

(
'Votre demande acceptée',
FALSE,
4,
1
);