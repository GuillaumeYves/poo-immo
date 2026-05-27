# poo-immo

Exercice IT-Akademy : application PHP orientée objet d'un catalogue d'annonces immobilières pour s'entrainer sur les pratiques POO.

## Lancement

Depuis la racine du projet :

```bash
php -S localhost:8000
```

Puis ouvrir [http://localhost:8000](http://localhost:8000).

## Structure du projet

poo-immo/
├── index.php                           # Point d'entrée : bootstrap, rendu HTML, routage export
├── assets/
│   └── js/
│       └── recherche.js               # Filtre live côté client (sous-chaîne)
├── data/                               # Fixtures hors du code source
│   ├── biens.seed.json                # Seed de biens (indexés par id)
│   └── annonces.seed.json             # Seed d'annonces (référencent les biens par bienId)
└── src/
    ├── autoload.php                    # Autoloader (mappe App\ vers src/)
    ├── Entity/                         # Coeur métier (survit à un changement de persistance)
    │   ├── BienImmo.php                 # Abstract : ville, surface, chambres + méthodes d'affichage/export
    │   ├── Appartement.php             # BienImmo + étage + ::fromArray()
    │   ├── Maison.php                  # BienImmo + terrain + ::fromArray()
    │   ├── Annonce.php                 # Abstract : bien, date, état + méthodes d'affichage/export
    │   ├── AnnonceVente.php            # Annonce + prix + ::fromArray()
    │   ├── AnnonceLocation.php         # Annonce + loyer/charges + ::fromArray()
    │   ├── EtatAnnonce.php             # Enum
    │   └── AnnonceRepositoryInterface.php  # Port du domaine (le seul qui compte pour futur ajout MySQL)
    ├── Database/
    │   └── JsonDataRepository.php      # Implémentation actuelle : lit le dossier /data
    ├── Presenter/
    │   ├── AnnoncePresenter.php        # Présente une annonce (mise en forme pour template)
    │   └── CataloguePresenter.php      # Présente l'ensemble (entête + itération)
    ├── Formatter/
    │   └── MoneyFormatter.php
    └── Exporter/
        ├── ExporterInterface.php
        ├── AnnonceArrayConverter.php
        ├── JsonExporter.php
        └── CsvExporter.php

## Namespaces et autoload

Toutes les classes vivent sous le namespace racine `App\`, organisé en sous-namespaces calqués sur l'arborescence (`App\Entity`, `App\Database`, `App\Presenter`, `App\Formatter`, `App\Exporter`).

L'autoloader maison dans `src/autoload.php` enregistre une callback `spl_autoload_register` qui mappe le préfixe `App\` vers le dossier `src/` :

```php
// App\Entity\BienImmo  =>  src/Entity/BienImmo.php
```

`index.php` ne contient donc qu'un seul `require_once` (celui de l'autoloader) au lieu d'une dizaine.

## Typage PHP

- `declare(strict_types=1);` en tête de chaque fichier (refus des conversions implicites).
- Types nullable (`?Type`) : `findOneByVille(string $ville): ?Annonce`, `?DateTimeImmutable $datePublication = null`.
- Types union (`int|float`) sur les paramètres numériques pour accepter aussi bien `180000` que `180000.0`.
- Enum tiré (`enum EtatAnnonce: string`) pour les états métier d'une annonce avec un `match` exhaustif.
- Propriétés `readonly` sur tout ce qui est logiquement immuable après construction :
  - `BienImmo` : `ville`, `surface`, `chambres`, `description`
  - `Appartement::etage`, `Maison::terrain`
  - `Annonce::bien`, `Annonce::datePublication`
  - `AnnonceVente::prix`, `AnnonceLocation::loyer`, `AnnonceLocation::charges`
  - `CsvExporter::separator`, `CsvExporter::withBom`
- Seul `Annonce::etat` reste mutable, car le cycle de vie d'une annonce (disponible, en négociation, indisponible) implique de pouvoir le changer après publication via `setEtat()`.
- Les setters de validation ont été supprimés ; la validation est inlinée dans le constructeur.

## Seed JSON

Le catalogue n'est pas codé en dur dans `index.php`. Biens et annonces sont décrits dans deux fichiers séparés sous `data/`, à la manière de deux tables relationnelles liées par un id.

### `biens.seed.json`

Tableau de biens, chacun identifié par un `id` (clé étrangère utilisée côté annonces) :

```json
[
  {
    "id": "lyon-01",
    "type": "appartement",
    "ville": "Lyon",
    "surface": 45,
    "chambres": 2,
    "etage": 3
  }
]
```

| Clé            | Valeurs                                           |
| --------------- | ------------------------------------------------- |
| `id`          | string unique dans le fichier                     |
| `type`        | `"appartement"` ou `"maison"`                 |
| `ville`       | string non vide                                   |
| `surface`     | nombre > 0                                        |
| `chambres`    | entier >= 0                                       |
| `etage`       | (appartement, optionnel) entier >= 0, défaut = 0 |
| `terrain`     | (maison, requis) nombre > 0                       |
| `description` | (optionnel)                                       |

### `annonces.seed.json`

Tableau d'annonces référençant un bien par son `bienId` :

```json
[
  {
    "bienId": "lyon-01",
    "transaction": "vente",
    "etat": "disponible",
    "prix": 180000
  }
]
```

| Clé                | Valeurs                                                    |
| ------------------- | ---------------------------------------------------------- |
| `bienId`          | doit exister dans `data/biens.seed.json`                 |
| `transaction`     | `"vente"` ou `"location"`                              |
| `etat`            | `"disponible"`, `"en_negociation"`, `"indisponible"` |
| `datePublication` | (optionnel) ISO 8601, défaut = maintenant                 |
| `prix`            | requis si `transaction = vente`                          |
| `loyer`           | requis si `transaction = location`                       |
| `charges`         | (optionnel) défaut = 0                                    |

### Orchestration : `JsonDataRepository`

Un seul service infra (`App\Database\JsonDataRepository`) implémente `AnnonceRepositoryInterface` et fait tout le travail :

```php
$repository = new JsonDataRepository(__DIR__ . '/data');
```

Sous le capot :

1. Lit `biens.seed.json`. Pour chaque ligne, regarde `type` dans une registry `[string => class-string]` interne et appelle `Classe::fromArray($row)` (constructeur nommé sur l'entité). Retourne `array<string, BienImmo>` indexé par id.
2. Lit `annonces.seed.json`. Résout `bienId` contre la map précédente (orphelin = `RuntimeException`), résout `etat` (enum) et `datePublication`, puis appelle `Classe::fromArray($row, $bien, $etat, $date)` selon la transaction.
3. Les annonces hydratées sont stockées dans une collection interne — l'instance est elle-même le repository (typée `AnnonceRepositoryInterface`).

Ajouter un nouveau type (ex: `Terrain`) :

1. Créer `src/Entity/Terrain.php` avec sa méthode statique `::fromArray()`.
2. Ajouter une ligne dans la registry `JsonDataRepository::BIEN_TYPES` : `'terrain' => Terrain::class`.

Aucun autre fichier modifié. Voir [`SOLID-refactor.md`](SOLID-refactor.md) pour le cheminement complet.

### Brancher MySQL plus tard

`AnnonceRepositoryInterface` vit dans `Entity/` côté domaine, c'est le seul contrat que le reste du code (presenters, exporters) connaît. Le jour où l'on passe à MySQL :

```php
// 1. Créer src/Database/MysqlAnnonceRepository.php qui implémente AnnonceRepositoryInterface
// 2. Une seule ligne change dans index.php :
$repository = new MysqlAnnonceRepository($pdo);
```

Le domaine, les presenters et les exporters ne bougent pas. `JsonDataRepository` peut être conservé pour les tests/dev.

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

Le filtrage est purement visuel : les exports JSON et CSV continuent de contenir toutes les annonces du repository.

## Export du catalogue

Deux formats disponibles, accessibles depuis les boutons en haut de la page ou directement via URL :

| Format | URL              | Fichier généré             |
| ------ | ---------------- | ----------------------------- |
| JSON   | `?export=json` | `catalogue-AAAA-MM-JJ.json` |
| CSV    | `?export=csv`  | `catalogue-AAAA-MM-JJ.csv`  |

Le CSV utilise `;` comme séparateur et un BOM UTF-8 (compatible Excel en français).
