-- LOCATECH / location_db
CREATE DATABASE IF NOT EXISTS location_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE location_db;

DROP TABLE IF EXISTS location;
DROP TABLE IF EXISTS equipement;
DROP TABLE IF EXISTS categorie;
DROP TABLE IF EXISTS utilisateur;

CREATE TABLE utilisateur (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'agent', 'responsable') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorie (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE equipement (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT NULL,
    prix_jour DECIMAL(10,2) NOT NULL,
    quantite_stock INT NOT NULL DEFAULT 0,
    seuil_alerte INT NOT NULL DEFAULT 1,
    etat ENUM('disponible', 'en location', 'en maintenance', 'endommagé') NOT NULL DEFAULT 'disponible',
    categorie_id INT UNSIGNED NOT NULL,
    photo VARCHAR(255) NULL DEFAULT NULL,
    CONSTRAINT fk_equipement_categorie
        FOREIGN KEY (categorie_id) REFERENCES categorie(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE location (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('en attente', 'confirmée', 'en cours', 'terminée', 'annulée') NOT NULL DEFAULT 'en attente',
    montant_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    frais_additionnels DECIMAL(10,2) NOT NULL DEFAULT 0,
    utilisateur_id INT UNSIGNED NOT NULL,
    equipement_id INT UNSIGNED NOT NULL,
    CONSTRAINT fk_location_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_location_equipement
        FOREIGN KEY (equipement_id) REFERENCES equipement(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO utilisateur (nom, prenom, email, password, role) VALUES
('Martin', 'Claire', 'responsable@location.local', '$2y$10$oTpRL3aIOg2WnWJ8ZCgrjOJDPw8pUvDnCfpVXxPrBNuUKe1jKDWny', 'responsable'),
('Dupont', 'Hugo', 'agent@location.local', '$2y$10$oTpRL3aIOg2WnWJ8ZCgrjOJDPw8pUvDnCfpVXxPrBNuUKe1jKDWny', 'agent'),
('Bernard', 'Léa', 'client@location.local', '$2y$10$oTpRL3aIOg2WnWJ8ZCgrjOJDPw8pUvDnCfpVXxPrBNuUKe1jKDWny', 'client');

INSERT INTO categorie (nom, description) VALUES
('Outillage électroportatif', 'Perceuses, meuleuses, visseuses et accessoires.'),
('Machines de chantier', 'Compacteurs, bétonnières et petits engins.'),
('Échafaudage et levage', 'Tours, échelles et palans.'),
('Énergie et éclairage', 'Groupes électrogènes et projecteurs.');

INSERT INTO equipement (nom, description, prix_jour, quantite_stock, seuil_alerte, etat, categorie_id) VALUES
('Perceuse percussion 18V', 'Perceuse-visseuse batterie, 2 batteries incluses.', 18.50, 8, 2, 'disponible', 1),
('Meuleuse 125 mm', 'Meuleuse d''angle professionnelle.', 14.00, 5, 2, 'disponible', 1),
('Bétonnière 160 L', 'Bétonnière électrique pour petits chantiers.', 35.00, 2, 1, 'disponible', 2),
('Compacteur à plaque', 'Plaque vibrante 90 kg.', 48.00, 1, 1, 'en maintenance', 2),
('Échafaudage roulant 6 m', 'Tour roulante aluminium, garde-corps inclus.', 42.00, 3, 1, 'disponible', 3),
('Groupe électrogène 5 kVA', 'Groupe essence insonorisé.', 55.00, 2, 1, 'disponible', 4);

INSERT INTO location (date_debut, date_fin, statut, montant_total, frais_additionnels, utilisateur_id, equipement_id) VALUES
(CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'en attente', 37.00, 0, 3, 1);
