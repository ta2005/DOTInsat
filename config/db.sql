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
    
    etudiant_id INT REFERENCES etudiant(id)
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




-- USERS  (1-2 admins | 3-6 profs | 7-16 étudiants)

INSERT INTO users (cin, nom, prenom, email, mot_passe) VALUES
(12345678, 'Ben Salah',  'Ahmed',   'ahmed.bensalah@insat.tn',           'hashed_pw_1'),
(23456789, 'Trabelsi',   'Fatma',   'fatma.trabelsi@insat.tn',           'hashed_pw_2'),
(34567890, 'Chaabane',   'Mohamed', 'mohamed.chaabane@insat.tn',         'hashed_pw_3'),
(45678901, 'Bouaziz',    'Sana',    'sana.bouaziz@insat.tn',             'hashed_pw_4'),
(56789012, 'Meddeb',     'Youssef', 'youssef.meddeb@insat.tn',           'hashed_pw_5'),
(67890123, 'Jebali',     'Ines',    'ines.jebali@insat.tn',              'hashed_pw_6'),
(78901234, 'Gharbi',     'Khalil',  'khalil.gharbi@etudiant.insat.tn',   'hashed_pw_7'),
(89012345, 'Hammami',    'Amira',   'amira.hammami@etudiant.insat.tn',   'hashed_pw_8'),
(90123456, 'Baccar',     'Nizar',   'nizar.baccar@etudiant.insat.tn',    'hashed_pw_9'),
(11223344, 'Sfar',       'Rania',   'rania.sfar@etudiant.insat.tn',      'hashed_pw_10'),
(22334455, 'Mzoughi',    'Tarek',   'tarek.mzoughi@etudiant.insat.tn',   'hashed_pw_11'),
(33445566, 'Dridi',      'Leila',   'leila.dridi@etudiant.insat.tn',     'hashed_pw_12'),
(44556677, 'Ayari',      'Bilel',   'bilel.ayari@etudiant.insat.tn',     'hashed_pw_13'),
(55667788, 'Tlili',      'Mariem',  'mariem.tlili@etudiant.insat.tn',    'hashed_pw_14'),
(66778899, 'Ben Amor',   'Oussama', 'oussama.benamor@etudiant.insat.tn', 'hashed_pw_15'),
(77889900, 'Khelifi',    'Cyrine',  'cyrine.khelifi@etudiant.insat.tn',  'hashed_pw_16');


-- ADMIN  (id 1, 2)

INSERT INTO admin (id, titre) VALUES
(1, 'Directeur des Études'),
(2, 'Responsable Scolarité');


-- PROFESSEUR  (id 3 → 6)

INSERT INTO professeur (id) VALUES (3), (4), (5), (6);


-- ETUDIANT  (id 7 → 16)

INSERT INTO etudiant (id, niveau_scolaire_info) VALUES
(7,  ROW('GL2-1',  2024, '2', 'GL' )::niveau_scolaire),
(8,  ROW('GL2-2',  2024, '2', 'GL' )::niveau_scolaire),
(9,  ROW('RT3-1',  2024, '3', 'RT' )::niveau_scolaire),
(10, ROW('RT3-2',  2024, '3', 'RT' )::niveau_scolaire),
(11, ROW('IIA4-1', 2024, '4', 'IIA')::niveau_scolaire),
(12, ROW('IIA4-1', 2024, '4', 'IIA')::niveau_scolaire),
(13, ROW('IMI2-1', 2024, '2', 'IMI')::niveau_scolaire),
(14, ROW('GL3-2',  2024, '3', 'GL' )::niveau_scolaire),
(15, ROW('RT2-1',  2024, '2', 'RT' )::niveau_scolaire),
(16, ROW('IMI4-1', 2024, '4', 'IMI')::niveau_scolaire);


-- MATIERES

INSERT INTO matieres (filiere, niveau, semestre, nom_matiere, coefficient, types_controle) VALUES
('GL',  2, 1, 'Algèbre',    2.00, ARRAY['DS', 'EXAM']::type_controle[]),
('GL',  2, 1, 'Analyse',    2.00, ARRAY['DS', 'EXAM']::type_controle[]),
('GL',  2, 2, 'Java',       3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('GL',  3, 1, 'BDD',        3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('GL',  3, 2, 'Conception', 2.50, ARRAY['DS', 'EXAM']::type_controle[]),
('RT',  2, 1, 'Réseaux',    3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('RT',  2, 1, 'Algèbre',    2.00, ARRAY['DS', 'EXAM']::type_controle[]),
('RT',  3, 1, 'BDD',        3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('IIA', 4, 1, 'Conception', 2.50, ARRAY['DS', 'EXAM']::type_controle[]),
('IIA', 4, 2, 'Dev Web',    3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('IMI', 2, 1, 'Dev Web',    3.00, ARRAY['DS', 'EXAM', 'TP']::type_controle[]),
('IMI', 4, 2, 'Réseaux',    3.00, ARRAY['DS', 'EXAM']::type_controle[]);


-- ENSEIGNEMENT

INSERT INTO enseignement (nom, date_debut, niveau_scolaire_info, professeur_id, matiere_id) VALUES
('Algèbre GL2',    '2024-09-15 08:00:00', ROW('GL2-1',  2024, '2', 'GL' )::niveau_scolaire, 3, 1),
('Analyse GL2',    '2024-09-15 10:00:00', ROW('GL2-2',  2024, '2', 'GL' )::niveau_scolaire, 4, 2),
('Java GL2',       '2024-09-16 08:00:00', ROW('GL2-1',  2024, '2', 'GL' )::niveau_scolaire, 3, 3),
('BDD GL3',        '2024-09-16 10:00:00', ROW('GL3-2',  2024, '3', 'GL' )::niveau_scolaire, 5, 4),
('Conception GL3', '2024-09-17 08:00:00', ROW('GL3-2',  2024, '3', 'GL' )::niveau_scolaire, 6, 5),
('Réseaux RT2',    '2024-09-17 10:00:00', ROW('RT2-1',  2024, '2', 'RT' )::niveau_scolaire, 5, 6),
('BDD RT3',        '2024-09-18 08:00:00', ROW('RT3-1',  2024, '3', 'RT' )::niveau_scolaire, 5, 8),
('Conception IIA', '2024-09-18 10:00:00', ROW('IIA4-1', 2024, '4', 'IIA')::niveau_scolaire, 6, 9),
('Dev Web IMI2',   '2024-09-19 08:00:00', ROW('IMI2-1', 2024, '2', 'IMI')::niveau_scolaire, 4, 11);


-- CONTROLE  (etudiant_id est maintenant direct sur la table)

INSERT INTO controle (note, type, statut, format, enseignement_id, etudiant_id) VALUES
-- Algèbre GL2
(14.50, 'DS',   'VERIFIE',    'QCM',     1, 7),
(11.00, 'DS',   'CORRIGE',    'QCM',     1, 8),
(16.00, 'EXAM', 'VERIFIE',    'MIX',     1, 7),
(13.50, 'EXAM', 'VERIFIE',    'MIX',     1, 8),
-- Analyse GL2
(16.75, 'DS',   'VERIFIE',    'NON_QCM', 2, 7),
(12.00, 'DS',   'CORRIGE',    'NON_QCM', 2, 8),
-- Java GL2
( 7.25, 'TP',   'CORRIGE',    'NON_QCM', 3, 7),
(15.00, 'TP',   'VERIFIE',    'NON_QCM', 3, 8),
-- BDD GL3
( 9.50, 'DS',   'CONTESTE',   'QCM',     4, 14),
(13.00, 'EXAM', 'EN_ATTENTE', 'MIX',     4, 14),
-- Conception GL3
(18.00, 'DS',   'VERIFIE',    'NON_QCM', 5, 14),
-- Réseaux RT2
(12.50, 'DS',   'VERIFIE',    'QCM',     6, 15),
(10.00, 'DS',   'CONTESTE',   'QCM',     6, 9),
-- BDD RT3
(11.50, 'DS',   'CORRIGE',    'MIX',     7, 9),
(14.00, 'EXAM', 'EN_ATTENTE', 'MIX',     7, 10),
-- Conception IIA
(17.00, 'DS',   'VERIFIE',    'NON_QCM', 8, 11),
(15.50, 'DS',   'VERIFIE',    'NON_QCM', 8, 12),
-- Dev Web IMI2
(13.00, 'TP',   'EN_ATTENTE', 'NON_QCM', 9, 13);


-- RECLAMATION  (référence controle_id directement)

INSERT INTO reclamation (message, controle_id, statut, admin_id) VALUES
('Ma note de DS BDD ne correspond pas à ma copie corrigée.',       9,  'EN_ATTENTE',                    1),
('Je conteste ma note de DS Réseaux, le corrigé est illisible.',   13, 'REFUSEE_PAR_LE_PROFESSEUR',     2),
('Erreur de calcul sur mon TP Java, deux questions non notées.',   7,  'ACCEPTEE_PAR_LE_PROFESSEUR',    1),
('Ma note d''EXAM BDD GL3 n''a pas été saisie correctement.',      10, 'EN_ATTENTE',                    2);


-- DEMANDE  (etudiant_id à la place de user_id)

INSERT INTO demande (message, type, statut, etudiant_id, admin_id) VALUES
('Attestation d''inscription pour dossier bancaire.',  'ATTESTATION_DE_INSCRIPTION', 'EN_ATTENTE', 7,  1),
('Feuilles de stage pour mon PFE entreprise.',         'FEUILLES_DE_STAGE',          'ACCEPTEE',   9,  2),
('Attestation de présence pour bourse CNSS.',          'ATTESTATION_DE_PRESENCE',    'EN_ATTENTE', 11, 1),
('Feuilles de notes du semestre 1.',                   'FEUILLES_DE_NOTES',          'REFUSEE',    15, 2),
('Attestation d''inscription pour demande de visa.',   'ATTESTATION_DE_INSCRIPTION', 'ACCEPTEE',   16, 1),
('Demande de document pour concours externe.',         'AUTRES',                     'EN_ATTENTE', 13, 1);


-- GROUPE

INSERT INTO groupe (nom, moderateur_id) VALUES
('GL2 - Entraide Cours',    7),
('RT3 - Révisions Réseaux', 9),
('IIA4 - Projets & PFE',    11),
('Forum Général INSAT',     1);


-- MEMBRE_GROUPE

INSERT INTO membre_groupe (user_id, groupe_id) VALUES
(7,  1), (8,  1), (13, 1), (14, 1),
(9,  2), (10, 2), (15, 2),
(11, 3), (12, 3), (16, 3),
(7,  4), (9,  4), (11, 4), (3,  4), (1,  4);


-- POST

INSERT INTO post (contenu, groupe_id, auteur_id) VALUES
('Quelqu''un a le corrigé du DS Algèbre de l''an dernier ?',   1, 7),
('Rappel : TP Réseaux demain 8h salle B12 !',                  2, 9),
('Je partage mes notes de cours Conception UML, dispo en MP.', 3, 11),
('Bienvenue sur le forum INSAT ! Présentez-vous 👋',           4, 1),
('Est-ce que l''exam Java couvre les threads aussi ?',          1, 14),
('Résultats BDD RT3 affichés sur l''ENT ce matin.',             2, 10);


-- COMMENTAIRE

INSERT INTO commentaire (contenu, post_id, auteur_id) VALUES
('Oui je l''ai, je t''envoie en privé.',          1, 8),
('Merci pour le rappel, j''avais oublié !',       2, 10),
('Super partage, merci beaucoup !',               3, 12),
('Salut tout le monde, GL2-2 ici !',              4, 8),
('Non, les threads ne sont pas au programme.',    5, 13),
('J''ai eu 14, super content !',                  6, 9);

-- VOTE

INSERT INTO vote (type, post_id, user_id) VALUES
('UPVOTE',   1, 8),
('UPVOTE',   1, 13),
('UPVOTE',   2, 10),
('DOWNVOTE', 2, 15),
('UPVOTE',   3, 12),
('UPVOTE',   3, 16),
('UPVOTE',   4, 7),
('UPVOTE',   5, 8),
('DOWNVOTE', 5, 13),
('UPVOTE',   6, 9);


-- NOTIFICATION

INSERT INTO notification (message, lue, user_id, createur_id) VALUES
('Votre demande d''attestation a été acceptée.',              FALSE, 9,  2),
('Votre réclamation DS BDD est en cours de traitement.',      FALSE, 14, 1),
('Nouveau post dans le groupe GL2.',                          TRUE,  8,  7),
('Votre demande de feuilles de notes a été refusée.',         FALSE, 15, 2),
('Nouveau commentaire sur votre post.',                       TRUE,  7,  8),
('Votre réclamation TP Java a été acceptée par le prof.',     FALSE, 7,  3),
('Résultats examen BDD GL3 publiés sur l''ENT.',              FALSE, 14, 5),
('Bienvenue sur la plateforme INSAT !',                       TRUE,  16, 1),
('Votre réclamation DS Réseaux a été refusée.',               FALSE, 9,  2),
('Nouvelle demande de stage soumise avec succès.',            TRUE,  9,  9);