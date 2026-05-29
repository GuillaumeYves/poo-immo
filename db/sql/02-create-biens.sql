/* Création de la table biens et insertion de données */
USE `poo_immo`;

DROP TABLE IF EXISTS `biens`;

CREATE TABLE `biens` (
    `id`          VARCHAR(64)                                                           NOT NULL,
    `categorie`   ENUM('appartement', 'maison', 'villa')                                NOT NULL,
    `type`        ENUM('Studio', 'T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7+')             NULL,
    `ville`       VARCHAR(120)                                                          NOT NULL,
    `surface`     DECIMAL(8, 2)                                                         NOT NULL,
    `chambres`    INT UNSIGNED                                                          NOT NULL,
    `etage`       INT UNSIGNED                                                          NULL,
    `terrain`     DECIMAL(10, 2)                                                        NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_biens_ville` (`ville`),
    INDEX `idx_biens_categorie` (`categorie`),
    INDEX `idx_biens_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `biens` (`id`, `categorie`, `type`, `ville`, `surface`, `chambres`, `etage`, `terrain`) VALUES
    ('lyon-01',        'appartement', 'T2', 'Lyon',         45,  2, 3,    NULL),
    ('paris-01',       'appartement', 'T1', 'Paris',        30,  1, 5,    NULL),
    ('marseille-01',   'appartement', 'T3', 'Marseille',    60,  3, 1,    NULL),
    ('toulouse-01',    'villa',       'T5', 'Toulouse',    120,  4, NULL, 500),
    ('bordeaux-01',    'maison',      'T4', 'Bordeaux',     95,  3, NULL, 200),
    ('nantes-01',      'appartement', 'T2', 'Nantes',       55,  2, 4,    NULL),
    ('strasbourg-01',  'villa',       'T5', 'Strasbourg',  110,  4, NULL, 350),
    ('montpellier-01', 'appartement', 'Studio', 'Montpellier', 38, 1, 2, NULL),
    ('lille-01',       'appartement', 'T3', 'Lille',        70,  3, 6,    NULL),
    ('rennes-01',      'villa',       'T6', 'Rennes',      130,  5, NULL, 600),
    ('nice-01',        'appartement', 'T2', 'Nice',         42,  2, 8,    NULL),
    ('grenoble-01',    'appartement', 'T3', 'Grenoble',     65,  3, 1,    NULL),
    ('dijon-01',       'maison',      'T4', 'Dijon',       105,  4, NULL, 420),
    ('angers-01',      'appartement', 'T2', 'Angers',       50,  2, 3,    NULL),
    ('reims-01',       'maison',      'T3', 'Reims',        90,  3, NULL, 250);
