--Enums:
CREATE TYPE type_demande AS ENUM ('ATTESTATION_DE_INSCRIPTION', 'ATTESTATION_DE_PRESENCE', 'FEUILLES_DE_STAGE', 'FEUILLES_DE_NOTES');
CREATE TYPE type_controle AS ENUM ('DS', 'EXAM', 'TP');
CREATE TYPE statut_note AS ENUM ('EN_ATTENTE', 'CORRIGE', 'VERIFIE', 'CONTESTE');
CREATE TYPE statut_requete AS ENUM ('EN_ATTENTE', 'ACCEPTEE', 'REFUSEE');
CREATE TYPE type_vote AS ENUM ('UPVOTE', 'DOWNVOTE');

-- Abstract Base Class: User
CREATE TABLE users (
    id UUID PRIMARY KEY,
    cin VARCHAR(50),
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_passe VARCHAR(255) NOT NULL
);

CREATE TYPE niveau_scolaire AS (
    classe VARCHAR(50),
    annee INT,
    niveau VARCHAR(50),
    filiere VARCHAR(100)
);

CREATE TABLE admin (
    id UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    titre VARCHAR(100)
);

CREATE TABLE professeur (
    id UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE etudiant (
    id UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    niveau_scolaire_info niveau_scolaire 
);


--Academic & Core Entities
CREATE TABLE enseignement (
    id UUID PRIMARY KEY,
    nom VARCHAR(255),
    date_debut TIMESTAMP,
    niveau_scolaire_info niveau_scolaire,
    professeur_id UUID REFERENCES professeur(id)
);

CREATE TABLE controle (
    id UUID PRIMARY KEY,
    note NUMERIC(5, 2),
    type type_controle,
    statut statut_note,
    enseignement_id UUID REFERENCES enseignement(id)
);

CREATE TABLE reclamation (
    id UUID PRIMARY KEY,
    message TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type_controle type_controle,
    statut statut_reclamation,
    enseignement_id UUID REFERENCES enseignement(id),
    etudiant_id UUID REFERENCES etudiant(id)
);

CREATE TABLE demande (
    id UUID PRIMARY KEY,
    message TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type type_demande,
    statut statut_demande,
    user_id UUID REFERENCES users(id)
);

--Social & Community Entities

CREATE TABLE groupe (
    id UUID PRIMARY KEY,
    nom VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    moderateur_id UUID REFERENCES users(id)
);

-- Association class representing "MembreGroupe"
CREATE TABLE membre_groupe (
    id UUID PRIMARY KEY, 
    date_adhesion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id UUID REFERENCES users(id),
    groupe_id UUID REFERENCES groupe(id),
    UNIQUE(user_id, groupe_id)
);

CREATE TABLE post (
    id UUID PRIMARY KEY,
    contenu TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    groupe_id UUID REFERENCES groupe(id),
    auteur_id UUID REFERENCES users(id)
);

CREATE TABLE commentaire (
    id UUID PRIMARY KEY,
    contenu TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    post_id UUID REFERENCES post(id) ON DELETE CASCADE,
    auteur_id UUID REFERENCES users(id)
);

CREATE TABLE vote (
    id UUID PRIMARY KEY,
    type type_vote,
    post_id UUID REFERENCES post(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    UNIQUE(post_id, user_id) 
);

CREATE TABLE notification (
    id UUID PRIMARY KEY,
    message TEXT,
    lue BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id UUID REFERENCES users(id), 
    createur_id UUID REFERENCES users(id) 
);