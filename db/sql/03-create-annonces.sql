/* Création de la table annonces et insertion de données */
USE `poo_immo`;

DROP TABLE IF EXISTS `annonces`;

CREATE TABLE `annonces` (
    `id`                    INT UNSIGNED                         NOT NULL AUTO_INCREMENT,
    `bien_id`               VARCHAR(64)                          NOT NULL,
    `titre`                 VARCHAR(160)                         NULL,
    `description`           TEXT                                 NULL,
    `transaction`           ENUM('vente', 'location')            NOT NULL,
    `etat`                  ENUM('disponible', 'en_negociation', 'indisponible')
                                                                 NOT NULL DEFAULT 'disponible',
    `date_publication`      DATETIME                             NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `derniere_modification` DATETIME                             NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                                 ON UPDATE CURRENT_TIMESTAMP,
    `prix_initial`          DECIMAL(12, 2)                       NULL,
    `prix_courant`          DECIMAL(12, 2)                       NULL,
    `loyer_initial`         DECIMAL(10, 2)                       NULL,
    `loyer`                 DECIMAL(10, 2)                       NULL,
    `charges_initiales`     DECIMAL(10, 2)                       NULL,
    `charges`               DECIMAL(10, 2)                       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_annonces_bien_id` (`bien_id`),
    CONSTRAINT `fk_annonces_bien`
        FOREIGN KEY (`bien_id`) REFERENCES `biens` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX `idx_annonces_transaction` (`transaction`),
    INDEX `idx_annonces_etat` (`etat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `annonces`
    (`bien_id`, `titre`, `description`, `transaction`, `etat`,
     `prix_initial`, `prix_courant`,
     `loyer_initial`, `loyer`, `charges_initiales`, `charges`) VALUES
    ('lyon-01',        'Bel appartement lumineux quartier calme',         NULL, 'vente',    'disponible',     180000, 180000, NULL, NULL, NULL, NULL),
    ('paris-01',       'Studio cosy proche transports',                   NULL, 'vente',    'en_negociation', 320000, 320000, NULL, NULL, NULL, NULL),
    ('marseille-01',   'T3 vue mer, à louer',                             NULL, 'location', 'disponible',     NULL,   NULL,   850,  850,  120,  120),
    ('toulouse-01',    'Villa familiale avec grand jardin',               NULL, 'vente',    'indisponible',   380000, 380000, NULL, NULL, NULL, NULL),
    ('bordeaux-01',    'Maison de caractère centre-ville',                NULL, 'location', 'en_negociation', NULL,   NULL,   1200, 1200, 150,  150),
    ('nantes-01',      'Bel appartement rénové',                          NULL, 'vente',    'disponible',     245000, 245000, NULL, NULL, NULL, NULL),
    ('strasbourg-01',  'Villa contemporaine au calme',                    NULL, 'vente',    'disponible',     295000, 295000, NULL, NULL, NULL, NULL),
    ('montpellier-01', 'Studio fonctionnel proche fac',                   NULL, 'location', 'disponible',     NULL,   NULL,   620,  620,  80,   80),
    ('lille-01',       'T3 spacieux 6e étage',                            NULL, 'vente',    'en_negociation', 215000, 215000, NULL, NULL, NULL, NULL),
    ('rennes-01',      'Grande villa avec terrain',                       NULL, 'vente',    'disponible',     410000, 410000, NULL, NULL, NULL, NULL),
    ('nice-01',        'Appartement vue dégagée',                         NULL, 'location', 'disponible',     NULL,   NULL,   950,  950,  130,  130),
    ('grenoble-01',    'T3 lumineux proche centre',                       NULL, 'location', 'disponible',     NULL,   NULL,   780,  780,  95,   95),
    ('dijon-01',       'Maison familiale au calme',                       NULL, 'vente',    'disponible',     265000, 265000, NULL, NULL, NULL, NULL),
    ('angers-01',      'T2 rénové en centre-ville',                       NULL, 'location', 'en_negociation', NULL,   NULL,   690,  690,  110,  110),
    ('reims-01',       'Maison de ville à rafraîchir',                    NULL, 'vente',    'indisponible',   198000, 198000, NULL, NULL, NULL, NULL);
