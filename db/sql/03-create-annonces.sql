USE `poo_immo`;

DROP TABLE IF EXISTS `annonces`;

CREATE TABLE `annonces` (
    `id`                INT UNSIGNED                          NOT NULL AUTO_INCREMENT,
    `bien_id`           VARCHAR(64)                           NOT NULL,
    `transaction`       ENUM('vente', 'location')             NOT NULL,
    `etat`              ENUM('disponible', 'en_negociation', 'indisponible')
                                                              NOT NULL DEFAULT 'disponible',
    `date_publication`  DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `prix`              DECIMAL(12, 2)                        NULL,
    `loyer`             DECIMAL(10, 2)                        NULL,
    `charges`           DECIMAL(10, 2)                        NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_annonces_bien`
        FOREIGN KEY (`bien_id`) REFERENCES `biens` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX `idx_annonces_transaction` (`transaction`),
    INDEX `idx_annonces_etat` (`etat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `annonces` (`bien_id`, `transaction`, `etat`, `prix`, `loyer`, `charges`) VALUES
    ('lyon-01',        'vente',    'disponible',     180000, NULL, NULL),
    ('paris-01',       'vente',    'en_negociation', 320000, NULL, NULL),
    ('marseille-01',   'location', 'disponible',     NULL,   850,  120),
    ('toulouse-01',    'vente',    'indisponible',   380000, NULL, NULL),
    ('bordeaux-01',    'location', 'en_negociation', NULL,   1200, 150),
    ('nantes-01',      'vente',    'disponible',     245000, NULL, NULL),
    ('strasbourg-01',  'vente',    'disponible',     295000, NULL, NULL),
    ('montpellier-01', 'location', 'disponible',     NULL,   620,  80),
    ('lille-01',       'vente',    'en_negociation', 215000, NULL, NULL),
    ('rennes-01',      'vente',    'disponible',     410000, NULL, NULL),
    ('nice-01',        'location', 'disponible',     NULL,   950,  130),
    ('grenoble-01',    'location', 'disponible',     NULL,   780,  95),
    ('dijon-01',       'vente',    'disponible',     265000, NULL, NULL),
    ('angers-01',      'location', 'en_negociation', NULL,   690,  110),
    ('reims-01',       'vente',    'indisponible',   198000, NULL, NULL);
