-- ENUMS

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

CREATE TYPE statut_reclamation AS ENUM (
    'EN_ATTENTE',
    'ACCEPTEE_PAR_LE_PROFESSEUR',
    'ACCEPTEE_PAR_ADMINISTRATEUR',
    'REFUSEE_PAR_LE_PROFESSEUR',
    'REFUSEE_PAR_ADMINISTRATEUR'    
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


-- USERS

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

-- MATIERES

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


-- ENSEIGNEMENT

CREATE TABLE enseignement (

    id SERIAL PRIMARY KEY,

    nom VARCHAR(255) NOT NULL,

    date_debut TIMESTAMP,

    niveau_scolaire_info niveau_scolaire,

    professeur_id INT REFERENCES professeur(id),

    matiere_id INT REFERENCES matieres(id)
);


-- CONTROLE


CREATE TABLE controle (

    id SERIAL PRIMARY KEY,

    note NUMERIC(5,2) CHECK (note >= 0 AND note <= 20),

    type type_controle NOT NULL,

    statut statut_note NOT NULL,

    format format,

    enseignement_id INT REFERENCES enseignement(id),

    etudiant_id INT REFERENCES etudiant(id)
);


-- RECLAMATION


CREATE TABLE reclamation (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    controle_id INT REFERENCES controle(id),

    statut statut_reclamation,

    admin_id INT REFERENCES admin(id)
);


-- DEMANDE


CREATE TABLE demande (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    type type_demande,

    statut statut_requete,

    etudiant_id INT REFERENCES etudiant(id),

    admin_id INT REFERENCES admin(id)
);

-- GROUPE

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


-- POST


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


-- NOTIFICATION

CREATE TABLE notification (

    id SERIAL PRIMARY KEY,

    message TEXT NOT NULL,

    lue BOOLEAN DEFAULT FALSE,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    user_id INT REFERENCES users(id),

    createur_id INT REFERENCES users(id)
);



-- =========================
-- USERS
-- =========================

INSERT INTO users (cin, nom, prenom, email, mot_passe) VALUES
(11111111, 'Ben Salah', 'Ahmed', 'ahmed.admin@esprit.tn', 'admin123'),
(22222222, 'Trabelsi', 'Mouna', 'mouna.admin@esprit.tn', 'admin123'),

(33333333, 'Jlassi', 'Karim', 'karim.prof@esprit.tn', 'prof123'),
(44444444, 'Gharbi', 'Sonia', 'sonia.prof@esprit.tn', 'prof123'),
(55555555, 'Masmoudi', 'Ali', 'ali.prof@esprit.tn', 'prof123'),

(66666666, 'Ben Ali', 'Youssef', 'youssef.etu@esprit.tn', 'etud123'),
(77777777, 'Ayari', 'Lina', 'lina.etu@esprit.tn', 'etud123'),
(88888888, 'Chahed', 'Amine', 'amine.etu@esprit.tn', 'etud123'),
(99999999, 'Haddad', 'Mariem', 'mariem.etu@esprit.tn', 'etud123'),
(10101010, 'Kefi', 'Omar', 'omar.etu@esprit.tn', 'etud123');

-- =========================
-- ADMIN
-- ids auto générés :
-- 1,2 = admins
-- =========================

INSERT INTO admin (id, titre) VALUES
(1, 'Directeur des études'),
(2, 'Responsable scolarité');

-- =========================
-- PROFESSEUR
-- ids :
-- 3,4,5
-- =========================

INSERT INTO professeur (id) VALUES
(3),
(4),
(5);

-- =========================
-- ETUDIANT
-- ids :
-- 6,7,8,9,10
-- =========================

INSERT INTO etudiant (id, niveau_scolaire_info) VALUES
(6, ROW('INFO2A', 2025, '2EME', 'Informatique')),
(7, ROW('INFO2A', 2025, '2EME', 'Informatique')),
(8, ROW('INFO3B', 2025, '3EME', 'GL')),
(9, ROW('INFO1A', 2025, '1ERE', 'RT')),
(10, ROW('INFO3B', 2025, '3EME', 'GL'));

-- =========================
-- MATIERES
-- =========================

INSERT INTO matieres (
    filiere,
    niveau,
    semestre,
    nom_matiere,
    coefficient,
    types_controle
) VALUES

(
    'INFO',
    2,
    1,
    'Base de Donnees',
    3.0,
    ARRAY['DS', 'EXAM', 'TP']::type_controle[]
),

(
    'INFO',
    2,
    1,
    'Programmation Web',
    2.5,
    ARRAY['DS', 'TP']::type_controle[]
),

(
    'GL',
    3,
    2,
    'Architecture Logicielle',
    4.0,
    ARRAY['DS', 'EXAM']::type_controle[]
),

(
    'RT',
    1,
    1,
    'Algorithmique',
    2.0,
    ARRAY['DS', 'EXAM', 'TP']::type_controle[]
);

-- =========================
-- ENSEIGNEMENT
-- matiere ids :
-- 1,2,3,4
-- =========================

INSERT INTO enseignement (
    nom,
    date_debut,
    niveau_scolaire_info,
    professeur_id,
    matiere_id
) VALUES

(
    'BD INFO2',
    '2025-09-01',
    ROW('INFO2A', 2025, '2EME', 'Informatique'),
    3,
    1
),

(
    'WEB INFO2',
    '2025-09-02',
    ROW('INFO2A', 2025, '2EME', 'Informatique'),
    4,
    2
),

(
    'ARCHI GL3',
    '2025-09-05',
    ROW('INFO3B', 2025, '3EME', 'GL'),
    5,
    3
),

(
    'ALGO RT1',
    '2025-09-03',
    ROW('INFO1A', 2025, '1ERE', 'RT'),
    3,
    4
);

-- =========================
-- CONTROLE
-- enseignement ids :
-- 1,2,3,4
-- =========================

INSERT INTO controle (
    note,
    type,
    statut,
    format,
    enseignement_id,
    etudiant_id
) VALUES

(15.5, 'DS', 'CORRIGE', 'QCM', 1, 6),
(12.0, 'EXAM', 'VERIFIE', 'NON_QCM', 1, 6),
(18.0, 'TP', 'CORRIGE', 'MIX', 2, 6),

(9.5, 'DS', 'CONTESTE', 'QCM', 1, 7),
(11.0, 'TP', 'EN_ATTENTE', 'MIX', 2, 7),

(14.0, 'EXAM', 'VERIFIE', 'NON_QCM', 3, 8),
(16.0, 'DS', 'CORRIGE', 'QCM', 3, 10),

(7.5, 'DS', 'CONTESTE', 'NON_QCM', 4, 9);

-- =========================
-- RECLAMATION
-- controle ids :
-- 1..8
-- =========================

INSERT INTO reclamation (
    message,
    controle_id,
    statut,
    admin_id
) VALUES

(
    'Je pense que ma note de DS est incorrecte.',
    4,
    'EN_ATTENTE',
    1
),

(
    'La correction de lexamen contient une erreur.',
    8,
    'ACCEPTEE_PAR_LE_PROFESSEUR',
    2
),

(
    'Ma copie na pas ete bien corrigee.',
    1,
    'REFUSEE_PAR_ADMINISTRATEUR',
    1
);

-- =========================
-- DEMANDE
-- =========================

INSERT INTO demande (
    message,
    type,
    statut,
    etudiant_id,
    admin_id
) VALUES

(
    'Je veux une attestation dinscription.',
    'ATTESTATION_DE_INSCRIPTION',
    'ACCEPTEE',
    6,
    1
),

(
    'Besoin des feuilles de notes.',
    'FEUILLES_DE_NOTES',
    'EN_ATTENTE',
    7,
    2
),

(
    'Demande de feuilles de stage.',
    'FEUILLES_DE_STAGE',
    'REFUSEE',
    8,
    1
),

(
    'Autre demande administrative.',
    'AUTRES',
    'EN_ATTENTE',
    9,
    2
);

-- =========================
-- GROUPE
-- =========================

INSERT INTO groupe (
    nom,
    moderateur_id
) VALUES

('GL2 Community', 6),
('Club Robotique', 7);

-- =========================
-- MEMBRE_GROUPE
-- groupe ids :
-- 1,2
-- =========================

INSERT INTO membre_groupe (
    user_id,
    groupe_id
) VALUES

(6, 1),
(7, 1),
(8, 1),

(7, 2),
(9, 2),
(10, 2);

-- =========================
-- POST
-- =========================

INSERT INTO post (
    contenu,
    groupe_id,
    auteur_id
) VALUES

(
    'Quelquun a compris le TP de base de donnees ?',
    1,
    6
),

(
    'La reunion du club robotique est demain.',
    2,
    7
),

(
    'Partage des ressources pour le DS.',
    1,
    8
);

-- =========================
-- COMMENTAIRE
-- post ids :
-- 1,2,3
-- =========================

INSERT INTO commentaire (
    contenu,
    post_id,
    auteur_id
) VALUES

(
    'Oui je peux texpliquer.',
    1,
    7
),

(
    'Merci pour linfo.',
    2,
    9
),

(
    'Je vais envoyer des resumes.',
    3,
    10
);

-- =========================
-- VOTE
-- =========================

INSERT INTO vote (
    type,
    post_id,
    user_id
) VALUES

('UPVOTE', 1, 7),
('UPVOTE', 1, 8),
('DOWNVOTE', 2, 10),
('UPVOTE', 3, 6);

-- =========================
-- NOTIFICATION
-- =========================

INSERT INTO notification (
    message,
    lue,
    user_id,
    createur_id
) VALUES

(
    'Votre reclamation est en attente.',
    FALSE,
    7,
    1
),

(
    'Votre demande a ete acceptee.',
    TRUE,
    6,
    2
),

(
    'Nouveau commentaire sur votre post.',
    FALSE,
    8,
    7
),

(
    'Nouvelle note disponible.',
    FALSE,
    9,
    3
);