USE `poo_immo`;

DROP TABLE IF EXISTS `biens`;

CREATE TABLE `biens` (
    `id`          VARCHAR(64)                       NOT NULL,
    `type`        ENUM('appartement', 'maison')     NOT NULL,
    `ville`       VARCHAR(120)                      NOT NULL,
    `surface`     DECIMAL(8, 2)                     NOT NULL,
    `chambres`    INT UNSIGNED                      NOT NULL,
    `description` TEXT                              NULL,
    `etage`       INT UNSIGNED                      NULL,
    `terrain`     DECIMAL(10, 2)                    NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_biens_ville` (`ville`),
    INDEX `idx_biens_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `biens` (`id`, `type`, `ville`, `surface`, `chambres`, `etage`, `terrain`) VALUES
    ('lyon-01',        'appartement', 'Lyon',         45,  2, 3,    NULL),
    ('paris-01',       'appartement', 'Paris',        30,  1, 5,    NULL),
    ('marseille-01',   'appartement', 'Marseille',    60,  3, 1,    NULL),
    ('toulouse-01',    'maison',      'Toulouse',    120,  4, NULL, 500),
    ('bordeaux-01',    'maison',      'Bordeaux',     95,  3, NULL, 200),
    ('nantes-01',      'appartement', 'Nantes',       55,  2, 4,    NULL),
    ('strasbourg-01',  'maison',      'Strasbourg',  110,  4, NULL, 350),
    ('montpellier-01', 'appartement', 'Montpellier',  38,  1, 2,    NULL),
    ('lille-01',       'appartement', 'Lille',        70,  3, 6,    NULL),
    ('rennes-01',      'maison',      'Rennes',      130,  5, NULL, 600),
    ('nice-01',        'appartement', 'Nice',         42,  2, 8,    NULL),
    ('grenoble-01',    'appartement', 'Grenoble',     65,  3, 1,    NULL),
    ('dijon-01',       'maison',      'Dijon',       105,  4, NULL, 420),
    ('angers-01',      'appartement', 'Angers',       50,  2, 3,    NULL),
    ('reims-01',       'maison',      'Reims',        90,  3, NULL, 250);
