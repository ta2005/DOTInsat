DROP TABLE IF EXISTS notification, vote, commentaire, post, membre_groupe, groupe,
    demande, reclamation, controle, enseignement,
    etudiant, professeur, admin, users CASCADE;

DROP TYPE IF EXISTS type_demande, type_controle, statut_note, statut_requete,
    type_vote, format, niveau_scolaire CASCADE;

CREATE TYPE type_demande   AS ENUM ('ATTESTATION_DE_INSCRIPTION', 'ATTESTATION_DE_PRESENCE', 'FEUILLES_DE_STAGE','AUTRES');
CREATE TYPE type_controle  AS ENUM ('DS', 'EXAM', 'TP');
CREATE TYPE statut_note    AS ENUM ('EN_ATTENTE', 'CORRIGE', 'VERIFIE', 'CONTESTE');
CREATE TYPE statut_requete AS ENUM ('EN_ATTENTE', 'ACCEPTEE', 'REFUSEE');
CREATE TYPE type_vote      AS ENUM ('UPVOTE', 'DOWNVOTE');
CREATE TYPE format         AS ENUM ('QCM', 'MIX', 'NON_QCM');

CREATE TYPE niveau_scolaire AS (
    classe   VARCHAR(50),
    annee    INT,
    niveau   VARCHAR(50),
    filiere  VARCHAR(100)
);

CREATE TABLE users (
    id        SERIAL PRIMARY KEY,
    cin       INT,
    nom       VARCHAR(100),
    prenom    VARCHAR(100),
    email     VARCHAR(255) UNIQUE NOT NULL,
    mot_passe VARCHAR(255) NOT NULL
);

CREATE TABLE admin (
    id    INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    titre VARCHAR(100)
);

CREATE TABLE professeur (
    id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE etudiant (
    id                  INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    niveau_scolaire_info niveau_scolaire
);

CREATE TABLE enseignement (
    id                   SERIAL PRIMARY KEY,
    nom                  VARCHAR(255),
    date_debut           TIMESTAMP,
    niveau_scolaire_info niveau_scolaire,
    professeur_id        INT REFERENCES professeur(id)
);

CREATE TABLE controle (
    id              SERIAL PRIMARY KEY,
    note            NUMERIC(5, 2),
    type            type_controle,
    statut          statut_note,
    format          format,
    enseignement_id INT REFERENCES enseignement(id)
);

CREATE TABLE reclamation (
    id              SERIAL PRIMARY KEY,
    message         TEXT,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type_controle   type_controle,
    statut          statut_requete,
    enseignement_id INT REFERENCES enseignement(id),
    etudiant_id     INT REFERENCES etudiant(id),
    admin_id        INT REFERENCES admin(id)
);

CREATE TABLE demande (
    id            SERIAL PRIMARY KEY,
    message       TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type          type_demande,
    statut        statut_requete,
    user_id       INT REFERENCES users(id),
    admin_id      INT REFERENCES admin(id)
);

CREATE TABLE groupe (
    id            SERIAL PRIMARY KEY,
    nom           VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    moderateur_id INT REFERENCES users(id)
);

CREATE TABLE membre_groupe (
    id            SERIAL PRIMARY KEY,
    date_adhesion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id       INT REFERENCES users(id),
    groupe_id     INT REFERENCES groupe(id),
    UNIQUE(user_id, groupe_id)
);

CREATE TABLE post (
    id              SERIAL PRIMARY KEY,
    contenu         TEXT,
    date_de_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    groupe_id       INT REFERENCES groupe(id),
    auteur_id       INT REFERENCES users(id)
);

CREATE TABLE commentaire (
    id            SERIAL PRIMARY KEY,
    contenu       TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    post_id       INT REFERENCES post(id) ON DELETE CASCADE,
    auteur_id     INT REFERENCES users(id)
);

CREATE TABLE vote (
    id      SERIAL PRIMARY KEY,
    type    type_vote,
    post_id INT REFERENCES post(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id),
    UNIQUE(post_id, user_id)
);

CREATE TABLE notification (
    id            SERIAL PRIMARY KEY,
    message       TEXT,
    lue           BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id       INT REFERENCES users(id),
    createur_id   INT REFERENCES users(id)
);

INSERT INTO users (cin, nom, prenom, email, mot_passe) VALUES

(10000001, 'Mansouri',  'Khalil',   'khalil.mansouri@insat.tn',  '$2y$10$hashed_admin_pass'),

(20000001, 'Ben Ali',   'Ahmed',    'ahmed.benali@insat.tn',     '$2y$10$hashed_prof1_pass'),
(20000002, 'Trabelsi',  'Sonia',    'sonia.trabelsi@insat.tn',   '$2y$10$hashed_prof2_pass'),
(20000003, 'Meddeb',    'Karim',    'karim.meddeb@insat.tn',     '$2y$10$hashed_prof3_pass'),

(30000001, 'Graja',     'Houssem',  'houssem.graja@insat.tn',    '$2y$10$hashed_stud1_pass'),
(30000002, 'Sassi',     'Ons',      'ons.sassi@insat.tn',        '$2y$10$hashed_stud2_pass'),
(30000003, 'Khammar',   'Rayen',    'rayen.khammar@insat.tn',    '$2y$10$hashed_stud3_pass'),
(30000004, 'BenBelhassen','Souhail','souhail.benbelhassen@insat.tn','$2y$10$hashed_stud4_pass'),
(30000005, 'Zigni',     'Talel',    'talel.zigni@insat.tn',      '$2y$10$hashed_stud5_pass');

INSERT INTO admin       (id, titre) VALUES (1, 'Responsable pédagogique');
INSERT INTO professeur  (id)        VALUES (2), (3), (4);
INSERT INTO etudiant    (id, niveau_scolaire_info) VALUES
(5, ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire),
(6, ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire),
(7, ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire),
(8, ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire),
(9, ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire);

INSERT INTO enseignement (nom, date_debut, niveau_scolaire_info, professeur_id) VALUES
('Java Avancé',         '2025-09-01 08:00', ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire, 2),
('Algorithmique',       '2025-09-01 08:00', ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire, 3),
('Bases de Données',    '2025-09-01 08:00', ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire, 4),
('Réseaux',             '2025-09-01 08:00', ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire, 2),
('Mathématiques',       '2025-09-01 08:00', ROW('GL', 2, 'Licence', 'Génie Logiciel')::niveau_scolaire, 3);

INSERT INTO controle (note, type, statut, format, enseignement_id) VALUES

(14.00, 'DS',   'CORRIGE',  'QCM',     1),
(16.00, 'EXAM', 'VERIFIE',  'MIX',     1),

(12.00, 'DS',   'CORRIGE',  'NON_QCM', 2),
(11.00, 'EXAM', 'CONTESTE', 'NON_QCM', 2),

(17.00, 'DS',   'CORRIGE',  'QCM',     3),
(19.50, 'EXAM', 'VERIFIE',  'MIX',     3),

(10.00, 'DS',   'CORRIGE',  'NON_QCM', 4),
(13.00, 'EXAM', 'EN_ATTENTE','NON_QCM',4),

(15.00, 'DS',   'CORRIGE',  'NON_QCM', 5),
(14.00, 'EXAM', 'VERIFIE',  'NON_QCM', 5);

INSERT INTO reclamation (message, type_controle, statut, enseignement_id, etudiant_id, admin_id) VALUES
('Je pense que ma copie d''examen d''algorithmique n''a pas été correctement corrigée. Question 3 non comptée.', 'EXAM', 'EN_ATTENTE', 2, 6, 1),
('Ma note de DS de réseaux semble incorrecte par rapport à mon brouillon.', 'DS', 'ACCEPTEE', 4, 7, 1),
('Erreur de report de note pour l''examen de maths.', 'EXAM', 'REFUSEE', 5, 5, 1);

INSERT INTO demande (message, type, statut, user_id, admin_id) VALUES
('J''ai besoin d''une attestation d''inscription pour ma banque.', 'ATTESTATION_DE_INSCRIPTION', 'ACCEPTEE', 5, 1),
('Demande de feuilles de stage pour mon entreprise d''accueil.', 'FEUILLES_DE_STAGE', 'EN_ATTENTE', 6, 1),
('Relevé de notes officiel pour candidature master.', 'FEUILLES_DE_NOTES', 'EN_ATTENTE', 7, 1),
('Attestation de présence pour bourse CROUS.', 'ATTESTATION_DE_PRESENCE', 'REFUSEE', 8, 1);

INSERT INTO groupe (nom, moderateur_id) VALUES
('GL2 — Groupe d''étude Java',        5),
('Préparation Examens Algo',           6),
('Projet Web DOTInsat',                5);

INSERT INTO membre_groupe (user_id, groupe_id) VALUES
(5, 1), (6, 1), (7, 1), (8, 1),
(6, 2), (7, 2), (9, 2),
(5, 3), (6, 3), (7, 3), (8, 3), (9, 3);

INSERT INTO post (contenu, groupe_id, auteur_id) VALUES
('Quelqu''un peut m''expliquer les génériques en Java ? J''ai du mal avec les wildcards.', 1, 5),
('Voici mes notes de révision pour l''examen d''algo — chapitre graphes.', 2, 6),
('Réunion de projet vendredi 16h. Pensez à pousser vos branches avant !', 3, 5),
('J''ai trouvé un bug dans notre système de réclamation, regardez le ticket #42.', 3, 7);

INSERT INTO commentaire (contenu, post_id, auteur_id) VALUES
('Merci pour les notes ! Tu as couvert les arbres couvrants aussi ?', 2, 7),
('Oui, je les ai ajoutés en page 12.', 2, 6),
('Réunion confirmée de mon côté.', 3, 8),
('Je vais regarder le bug ce soir.', 4, 5);

INSERT INTO vote (type, post_id, user_id) VALUES
('UPVOTE', 2, 5), ('UPVOTE', 2, 7), ('UPVOTE', 2, 9),
('UPVOTE', 1, 6), ('DOWNVOTE', 4, 8);

INSERT INTO notification (message, lue, user_id, createur_id) VALUES
('Votre réclamation #1 a été reçue et est en cours de traitement.', false, 6, 1),
('Votre demande d''attestation d''inscription a été approuvée.', true,  5, 1),
('Votre réclamation de DS Réseaux a été acceptée.', false, 7, 1),
('Votre demande d''attestation de présence a été refusée. Motif : dossier incomplet.', false, 8, 1);