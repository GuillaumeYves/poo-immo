# poo-immo

Exercice IT-Akademy : application PHP orientée objet qui affiche un catalogue d'annonces immobilières depuis MySQL.

## Setup

Depuis la racine du projet.

### 1. Configurer les credentials

```bash
cp .env.exemple .env
```

Puis éditer `.env` pour renseigner les vraies valeurs :

```env
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

- `01-create-database.sql` : drop + create de la base `poo_immo`
- `02-create-biens.sql` : table `biens` + données de démonstration
- `03-create-annonces.sql` : table `annonces` + données de démonstration, avec clé étrangère vers `biens`

Les scripts sont ré-exécutables grâce aux `DROP IF EXISTS`. Relancer `php db/db.php` réinitialise donc complètement la base.

### 3. Lancer le serveur

```bash
php -S localhost:8000
```

Puis ouvrir [http://localhost:8000](http://localhost:8000).

### 4. Appliquer les réductions de démonstration

```bash
php bin/apply-reductions.php
```

Ce script applique plusieurs réductions sur des annonces de vente et écrit les notifications dans `logs/logs.txt`.

## Prérequis

- PHP 8.1 ou plus récent, car le projet utilise les `enum`, les propriétés `readonly`, les types union et `static` en retour.
- MySQL ou MariaDB pour la persistance.
- Extension PDO MySQL activée.
- Extension BCMath activée pour les calculs monétaires exacts.

### BCMath et montants monétaires

Les montants (`prix_initial`, `prix_avec_reduction`, `loyer`, `charges`) sont stockés en `DECIMAL` côté MySQL et manipulés en `string` côté PHP. PHP n'a pas de type décimal natif : utiliser `float` introduirait des erreurs d'arrondi sur les montants, par exemple avec des additions ou pourcentages répétés.

BCMath est donc utilisé pour conserver une précision au centime :

| Opération | À éviter | Utilisé dans le projet |
| --- | --- | --- |
| Addition | `$a + $b` | `bcadd($a, $b, 2)` |
| Soustraction | `$a - $b` | `bcsub($a, $b, 2)` |
| Multiplication | `$a * $b` | `bcmul($a, $b, 2)` |
| Division | `$a / $b` | `bcdiv($a, $b, 2)` |
| Comparaison | `$a <= 0` | `bccomp($a, '0', 2) <= 0` |

Le paramètre `2` correspond à la scale, donc au nombre de décimales conservées. Pour vérifier l'extension :

```bash
php -m | grep bcmath
```

Sous Windows PowerShell :

```powershell
php -m | Select-String bcmath
```

## Structure du Projet

```text
poo-immo/
├── index.php                         # Point d'entrée web : catalogue, recherche, exports
├── .env.exemple                      # Exemple de configuration locale
├── assets/
│   └── js/
│       └── recherche.js              # Filtre live côté client
├── bin/
│   └── apply-reductions.php          # Script CLI de démonstration des réductions
├── config/
│   └── database.php                  # Lit .env via EnvLoader et renvoie la config PDO
├── db/
│   ├── db.php                        # Runner SQL
│   └── sql/
│       ├── 01-create-database.sql
│       ├── 02-create-biens.sql
│       └── 03-create-annonces.sql
├── logs/
│   └── logs.txt                      # Généré par ReductionLogger
└── src/
    ├── autoload.php                  # Autoloader App\ vers src/
    ├── Config/
    │   └── EnvLoader.php             # Parseur .env sans dépendance externe
    ├── Database/
    │   ├── Database.php              # Singleton PDO
    │   └── AnnonceRepository.php     # Implémentation SQL du repository
    ├── Entity/
    │   ├── Annonce/
    │   │   ├── Annonce.php           # Classe abstraite commune
    │   │   ├── AnnonceVente.php
    │   │   ├── AnnonceLocation.php
    │   │   ├── EtatAnnonce.php       # Enum métier
    │   │   ├── AnnonceFactory.php    # Hydratation row SQL -> objets métier
    │   │   └── AnnonceRepositoryInterface.php
    │   └── Bien/
    │       ├── BienImmo.php          # Classe abstraite commune
    │       ├── Appartement.php
    │       └── Maison.php
    ├── Exporter/
    │   ├── ExporterInterface.php
    │   ├── AnnonceArrayConverter.php
    │   ├── JsonExporter.php
    │   └── CsvExporter.php
    ├── Formatter/
    │   └── MoneyFormatter.php
    ├── Logger/
    │   └── ReductionLogger.php
    ├── Observer/
    │   └── ReductionObserver.php
    ├── Presenter/
    │   ├── AnnoncePresenter.php
    │   └── CataloguePresenter.php
    ├── Reduction/
    │   └── Pourcentage.php
    └── Service/
        └── AppliqueReduction.php
```

Toutes les classes applicatives vivent sous le namespace racine `App\`. L'autoloader de `src/autoload.php` mappe ce préfixe vers le dossier `src/`, ce qui permet à `index.php` et aux scripts CLI de ne charger qu'un seul fichier.

Le schéma SQL applique une forme de Single Table Inheritance :

- `biens` contient les informations communes (`ville`, `surface`, `chambres`) et les colonnes spécifiques (`etage` pour un appartement, `terrain` pour une maison).
- `annonces` contient les informations communes (`transaction`, `etat`, `date_publication`) et les colonnes spécifiques (`prix_initial` / `prix_avec_reduction` pour une vente, `loyer` / `charges` pour une location).

Les entités exposent les types sous forme de codes métier neutres (`appartement`, `maison`, `vente`, `location`). Les libellés visibles (`Appartement`, `Maison`, `Vente`, `Location`) sont produits par `AnnoncePresenter`.

## Principes SOLID

### S - Single Responsibility Principle

Chaque classe a une responsabilité limitée :

- `AnnonceRepository` fait uniquement l'accès SQL.
- `AnnonceFactory` transforme une row SQL en objets métier.
- `AnnoncePresenter` et `CataloguePresenter` préparent les données pour l'affichage HTML.
- `JsonExporter` et `CsvExporter` gèrent seulement leur format d'export.
- `AppliqueReduction` orchestre le cas d'usage "appliquer une réduction".
- `ReductionLogger` écrit les notifications dans un fichier.

### O - Open/Closed Principle

Le projet est ouvert à l'extension sans modifier toute la chaîne existante :

- Ajouter un export revient à créer une classe qui implémente `ExporterInterface`, puis à l'enregistrer dans `index.php`.
- Ajouter un observer revient à créer une classe qui implémente `ReductionObserver`, puis à l'abonner avec `subscribe()`.
- Ajouter un type de bien ou d'annonce se fait d'abord via la classe concrète et le registre de `AnnonceFactory`. Si ce type a des champs spécifiques à afficher ou exporter, l'adaptation reste localisée dans `AnnoncePresenter` et `AnnonceArrayConverter`.

### L - Liskov Substitution Principle

Les classes concrètes peuvent remplacer leurs abstractions sans casser le code appelant :

- `Appartement` et `Maison` sont manipulés comme des `BienImmo`.
- `AnnonceVente` et `AnnonceLocation` sont manipulées comme des `Annonce`.
- Les presenters et exporters s'appuient sur les getters métier (`getPrixCourant()`, `getLoyerCharges()`, `getTerrain()`, etc.) et gardent la mise en forme hors des entités.

### I - Interface Segregation Principle

Les interfaces restent ciblées :

- `ExporterInterface` expose uniquement ce dont `index.php` a besoin pour exporter : contenu, content-type, nom de fichier et format.
- `AnnonceRepositoryInterface` décrit les opérations nécessaires au catalogue et au service de réduction.
- `ReductionObserver` ne contient qu'une méthode : `onReductionAppliquee()`.

### D - Dependency Inversion Principle

Les services de haut niveau dépendent de contrats plutôt que de détails :

- `AppliqueReduction` dépend de `AnnonceRepositoryInterface`, pas directement de `AnnonceRepository`.
- Les notifications passent par `ReductionObserver`, ce qui permet de remplacer le logger fichier par un mailer, un webhook ou une autre sortie.
- `index.php` compose les objets concrets au bord de l'application.

## Design Patterns

### Singleton → une seule instance

`App\Database\Database` garantit une seule instance de connexion PDO via `Database::getInstance()`. La propriété statique `$instance` conserve l'objet déjà créé, ce qui évite de rouvrir une connexion à chaque repository.

`Database::bootstrap()` utilise le même mécanisme mais se connecte sans `dbname`, pour permettre au runner `db/db.php` de créer ou recréer la base.

### Observer → notifications automatiques

`App\Service\AppliqueReduction` joue le rôle de Subject :

- `subscribe(ReductionObserver $observer)` ajoute un observer.
- `appliquer()` modifie le prix en base.
- `notifier()` déclenche automatiquement tous les observers après la réduction.

`App\Logger\ReductionLogger` implémente `ReductionObserver` et écrit chaque réduction dans `logs/logs.txt`. On peut ajouter d'autres notifications sans modifier le service.

### Strategy → comportement interchangeable

L'export utilise le Strategy pattern :

- `ExporterInterface` définit le contrat commun.
- `JsonExporter` fournit la stratégie JSON.
- `CsvExporter` fournit la stratégie CSV.
- `index.php` choisit la stratégie selon `?export=json` ou `?export=csv`.

Le code d'appel manipule donc un `ExporterInterface` et ne dépend pas du format concret.

### Factory → création d'objets centralisée

`App\Entity\Annonce\AnnonceFactory` centralise la création des objets métier depuis les lignes SQL :

- `type = appartement` crée un `Appartement`.
- `type = maison` crée une `Maison`.
- `transaction = vente` crée une `AnnonceVente`.
- `transaction = location` crée une `AnnonceLocation`.

Le repository ne connaît pas les classes concrètes à instancier : il récupère des rows SQL et délègue l'hydratation à la factory.

## Typage PHP

- `declare(strict_types=1);` est présent dans les fichiers PHP pour refuser les conversions implicites.
- Les propriétés immuables utilisent `readonly`, par exemple dans `BienImmo`, `Annonce`, `AnnonceVente`, `AnnonceLocation`, `Database` et `CsvExporter`.
- Les montants sont typés `string` pour rester compatibles avec `DECIMAL` et BCMath.
- Les valeurs physiques comme la surface ou le terrain utilisent `int|float`.
- Les retours nullable sont explicites, par exemple `findById(int $id): ?Annonce`.
- `EtatAnnonce` est un `enum EtatAnnonce: string`, avec un `match` pour produire le libellé métier.
- Les tableaux complexes sont documentés avec PHPDoc, par exemple `@return Annonce[]` ou `array<string, string|int|float|null>`.
- `Annonce::etat` reste volontairement mutable via `setEtat()`, car l'état d'une annonce peut évoluer après publication.

## Export

Deux formats sont disponibles depuis les boutons en haut du catalogue ou directement via URL :

| Format | URL              | Fichier généré             |
| ------ | ---------------- | ----------------------------- |
| JSON   | `?export=json` | `catalogue-AAAA-MM-JJ.json` |
| CSV    | `?export=csv`  | `catalogue-AAAA-MM-JJ.csv`  |

Le CSV utilise `;` comme séparateur et ajoute un BOM UTF-8 pour une meilleure compatibilité avec Excel en français.

Les exports utilisent les codes métier neutres (`appartement`, `maison`, `vente`, `location`) plutôt que les libellés affichés dans le catalogue.
