# poo-immo

Exercice IT-Akademy : application PHP orientée objet d'un catalogue d'annonces immobilières pour s'entrainer sur les pratiques POO.

## Setup

Depuis la racine du projet :

### 1. Configurer les credentials

```bash
cp .env.exemple .env
```

Puis éditer `.env` pour renseigner les vraies valeurs :

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=poo_immo
DB_USER=root
DB_PASSWORD=ton_mot_de_passe
DB_CHARSET=utf8mb4
```

### 2. Créer et peupler la base

```bash
php db/db.php
```

Le script exécute, dans l'ordre, tous les `.sql` de `db/sql/` :

- `01-create-database.sql` drop + create de la base `poo_immo`
- `02-create-biens.sql` table `biens` + 15 lignes seed
- `03-create-annonces.sql` table `annonces` + 15 lignes seed (FK vers `biens`)

Tous les scripts sont ré-exécutables (`DROP IF EXISTS`) : relancer `php db/db.php` reset complètement la base.

### 3. Lancer le serveur

```bash
php -S localhost:8000
```

Puis ouvrir [http://localhost:8000](http://localhost:8000).

## Structure du projet

```
poo-immo/
├── index.php                          # Point d'entrée
├── .env.exemple                                        
├── assets/
│   └── js/
│       └── recherche.js               # Filtre live côté client (sous-chaîne)
├── config/
│   └── database.php                   # Lit .env via EnvLoader et renvoie la config PDO
├── db/
│   ├── db.php                         # Runner : exécute tous les .sql dans l'ordre
│   └── sql/
│       ├── 01-create-database.sql
│       ├── 02-create-biens.sql
│       └── 03-create-annonces.sql
└── src/
    ├── autoload.php                   # Autoloader (mappe App\ vers src/)
    ├── Config/
    │   └── EnvLoader.php              # Parseur .env minimaliste (zéro dépendance)
    ├── Database/
    │   ├── Database.php               # Singleton PDO
    │   └── AnnonceRepository.php      # Impl. SQL de AnnonceRepositoryInterface
    ├── Entity/
    │   ├── Annonce/     
    │   │   ├── Annonce.php            # Abstract
    │   │   ├── AnnonceVente.php
    │   │   ├── AnnonceLocation.php
    │   │   ├── EtatAnnonce.php        # Enum
    │   │   ├── AnnonceFactory.php     # Factory; Row → objet Annonce (+ Bien)
    │   │   └── AnnonceRepositoryInterface.php
    │   └── Bien/  
    │       ├── BienImmo.php           # Abstract
    │       ├── Appartement.php
    │       └── Maison.php
    ├── Presenter/
    │   ├── AnnoncePresenter.php
    │   └── CataloguePresenter.php
    ├── Formatter/
    │   └── MoneyFormatter.php
    └── Exporter/
        ├── ExporterInterface.php
        ├── AnnonceArrayConverter.php
        ├── JsonExporter.php
        └── CsvExporter.php
```

## Namespaces et autoload

Toutes les classes vivent sous le namespace racine `App\`, organisé en sous-namespaces calqués sur l'arborescence (`App\Entity\Annonce`, `App\Entity\Bien`, `App\Database`, `App\Config`, `App\Presenter`, `App\Formatter`, `App\Exporter`).

L'autoloader maison dans `src/autoload.php` enregistre une callback `spl_autoload_register` qui mappe le préfixe `App\` vers le dossier `src/` :

```php
// App\Entity\Bien\BienImmo  =>  src/Entity/Bien/BienImmo.php
// App\Database\Database     =>  src/Database/Database.php
```

`index.php` ne contient donc qu'un seul `require_once` (celui de l'autoloader).

## Persistance : MySQL + Singleton + Factory

### Singleton de connexion

`App\Database\Database` est un singleton qui encapsule l'instance PDO. Deux entrées selon le contexte :

```php
Database::getInstance();   // connexion avec dbname (cas normal)
Database::bootstrap();     // connexion sans dbname (pour le runner db.php
                           // qui doit pouvoir DROP/CREATE DATABASE)
```

La config (host, port, dbname, etc.) est lue dans `.env` via `App\Config\EnvLoader` au moment de la première connexion. PDO est configuré en mode exceptions, fetch associatif, prepares non émulées.

### Repository

`App\Database\AnnonceRepository` implémente `AnnonceRepositoryInterface` (le contrat défini côté domaine, dans `Entity/Annonce/`). Il ne fait que du SQL : `BASE_SELECT` joint `annonces` + `biens`, chaque méthode `findBy*` exécute un prepared statement et délègue la construction à la factory.

### Factory

`App\Entity\Annonce\AnnonceFactory` reçoit une row (tableau associatif) et renvoie un `Annonce` typé (`AnnonceVente` ou `AnnonceLocation`) avec son `BienImmo` (`Appartement` ou `Maison`). La factory choisit la classe concrète à partir des discriminateurs `type` (bien) et `transaction` (annonce) via deux registries `[string => class-string]`.

Méthodes publiques : `hydrate(array $row): Annonce` et `hydrateAll(array $rows): array`.

L'intérêt de cette séparation : `AnnonceRepository` n'importe plus que `Annonce`, `AnnonceFactory` et l'interface, il ne connaît pas les classes concrètes. La factory est réutilisable avec n'importe quelle source de row (BDD, CSV, mock de test).

### Schéma SQL : Single Table Inheritance

L'héritage POO est aplati en deux tables avec colonnes discriminantes :

- `biens(id, type, ville, surface, chambres, description, etage, terrain)` `etage` et `terrain` nullables, chacun pertinent pour un seul `type`.
- `annonces(id, bien_id, transaction, etat, date_publication, prix, loyer, charges)` `prix` (vente) et `loyer`/`charges` (location) nullables. FK vers `biens` avec `ON DELETE CASCADE`.

### Ajouter un nouveau type

Pour ajouter un type de bien (ex: `Terrain`) :

1. Créer `src/Entity/Bien/Terrain.php` avec son `::fromArray()`.
2. Ajouter `'terrain' => Terrain::class` dans `AnnonceFactory::BIEN_TYPES`.
3. Ajouter la valeur à l'ENUM SQL (`ALTER TABLE biens MODIFY type ENUM('appartement', 'maison', 'terrain') NOT NULL;`).

Le repository, les presenters et les exporters ne bougent pas c'est le bénéfice du contrat `AnnonceRepositoryInterface` côté domaine.

## Précision monétaire : DECIMAL + BCMath

Les montants (`prix`, `loyer`, `charges`) sont **stockés en `DECIMAL` côté MySQL et manipulés en `string` côté PHP**, avec arithmétique exacte via l'extension BCMath.

### Pourquoi pas `float` ?

Le `float` PHP utilise IEEE 754 binaire et ne peut pas représenter exactement certaines valeurs décimales : `0.1 + 0.2 === 0.3` retourne `false`. Sur des additions/multiplications répétées (promotions cumulées, totaux), l'erreur s'accumule. `DECIMAL` côté BDD garantit la valeur exacte sur disque, mais PHP n'a pas de type décimal natif, donc on garde la string.

### Comment la valeur circule

```
DB : DECIMAL(12,2)  →  PDO renvoie "180000.00" (string)  →  fromArray cast (string)
                    →  propriétés `string` dans l'entité  →  calculs via bc*
                    →  MoneyFormatter : (float) UNIQUEMENT pour number_format()
```

Le seul endroit où on accepte de perdre la précision est `MoneyFormatter::format()`, parce qu'on n'affiche jamais plus de 2 décimales. Toute la chaîne en amont reste exacte.

### Patterns d'usage

| Opération     | float (à éviter)  | BCMath (à utiliser)         |
| ------------- | ----------------- | --------------------------- |
| Addition      | `$a + $b`         | `bcadd($a, $b, 2)`          |
| Soustraction  | `$a - $b`         | `bcsub($a, $b, 2)`          |
| Multiplication| `$a * $b`         | `bcmul($a, $b, 2)`          |
| Division      | `$a / $b`         | `bcdiv($a, $b, 2)`          |
| Comparaison   | `$a <= 0`         | `bccomp($a, '0', 2) <= 0`   |

Le paramètre `2` est la **scale** : nombre de décimales conservées. Comparer sans scale tronque à l'entier (`bccomp("0.50", "0")` retourne `0` = égal au lieu de `1`), d'où le `2` partout pour rester précis au centime.

### Prérequis

Extension `ext-bcmath` activée (incluse dans la plupart des distributions PHP, vérifie via `php -m | grep bcmath`).

## Typage PHP

- `declare(strict_types=1);` en tête de chaque fichier (refus des conversions implicites).
- Types nullable (`?Type`) : `findOneByVille(string $ville): ?Annonce`, `?DateTimeImmutable $datePublication = null`.
- Types union (`int|float`) sur les paramètres **physiques** (surface, terrain) pour accepter aussi bien `45` que `45.0`. Les montants **monétaires** sont en revanche typés `string` (cf. section « Précision monétaire »).
- Enum tiré (`enum EtatAnnonce: string`) pour les états métier d'une annonce avec un `match` exhaustif.
- Propriétés `readonly` sur tout ce qui est logiquement immuable après construction :
  - `BienImmo` : `ville`, `surface`, `chambres`, `description`
  - `Appartement::etage`, `Maison::terrain`
  - `Annonce::bien`, `Annonce::datePublication`
  - `AnnonceVente::prix`, `AnnonceLocation::loyer`, `AnnonceLocation::charges`
  - `CsvExporter::separator`, `CsvExporter::withBom`
- Seul `Annonce::etat` reste mutable, car le cycle de vie d'une annonce (disponible, en négociation, indisponible) implique de pouvoir le changer après publication via `setEtat()`.
- Les setters de validation ont été supprimés ; la validation est inlinée dans le constructeur.

## Recherche

Un champ de recherche en haut du catalogue filtre les annonces au fur et à mesure de la frappe, côté client, sans rechargement de page. La logique tient dans `assets/js/recherche.js`, chargé en `defer` depuis `index.php` pour bien séparer HTML et JS.

Comment ça marche :

- La saisie de l'utilisateur est comparée au titre de l'annonce via `String.prototype.includes()`, après passage des deux chaînes en minuscules pour rester insensible à la casse.
- Le filtre s'applique sur le titre de l'annonce (`Appartement à Lyon`, `Maison à Bordeaux`, etc.), exposé via l'attribut `data-titre` de chaque `<article>`.
- Le compteur `X / Y annonces` se met à jour à chaque frappe.

| Saisie          | Effet                                                 |
| --------------- | ----------------------------------------------------- |
| `lyon`        | les annonces dont le titre contient "lyon"            |
| `ly`          | "Lyon", mais aussi tout titre contenant la suite "ly" |
| `appartement` | toutes les annonces dont le titre contient ce mot     |

Le filtrage est purement visuel.

## Export du catalogue

Deux formats disponibles, accessibles depuis les boutons en haut de la page ou directement via URL :

| Format | URL              | Fichier généré             |
| ------ | ---------------- | ----------------------------- |
| JSON   | `?export=json` | `catalogue-AAAA-MM-JJ.json` |
| CSV    | `?export=csv`  | `catalogue-AAAA-MM-JJ.csv`  |

Le CSV utilise `;` comme séparateur et un BOM UTF-8 (compatible Excel en français).
