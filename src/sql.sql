CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    cin INT UNIQUE,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS groups (
    id SERIAL PRIMARY KEY,    
    nom VARCHAR(255) NOT NULL,
    date_creation DATE DEFAULT CURRENT_DATE,
    id_mod INT,
    CONSTRAINT fk_mod FOREIGN KEY(id_mod) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS post (
    id SERIAL PRIMARY KEY,
    contenu TEXT, 
    date_de_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    id_user INT,
    id_group INT,
    CONSTRAINT fk_user FOREIGN KEY(id_user) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_group FOREIGN KEY(id_group) REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS membre (
    id_user INT,
    id_group INT,
    date_debut DATE DEFAULT CURRENT_DATE,
    PRIMARY KEY (id_user, id_group), 
    CONSTRAINT fk_user FOREIGN KEY(id_user) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_group FOREIGN KEY(id_group) REFERENCES groups(id) ON DELETE CASCADE
);
