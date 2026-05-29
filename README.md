# poo-immo

Application PHP orientée objet pour gérer un catalogue d'annonces immobilières avec MySQL.
Le projet suit une architecture MVC simple, avec CRUD d'annonces, création de biens libres, recherche live, tri, filtres et exports JSON / CSV.

## Prérequis

- PHP 8.1 ou plus récent
- MySQL ou MariaDB
- Extensions PHP `pdo_mysql` et `bcmath`

BCMath est utilisé pour les montants, car les prix, loyers et charges sont stockés en `DECIMAL` en base et manipulés en `string` côté PHP. Cela évite les erreurs d'arrondi des `float`.

## Installation

Depuis la racine du projet :

```bash
cp .env.exemple .env
```

Renseigner ensuite les variables :

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=poo_immo
DB_USER=root
DB_PASSWORD=ton_mot_de_passe
DB_CHARSET=utf8mb4
```

Créer et peupler la base :

```bash
php db/db.php
```

Lancer le serveur PHP :

```bash
php -S localhost:8000
```

Puis ouvrir `http://localhost:8000`.

## Structure

```text
poo-immo/
|-- index.php
|-- assets/
|   |-- css/app.css
|   `-- js/
|       |-- modal.js
|       |-- view-toggle.js
|       |-- recherche.js
|       |-- multi-picker.js
|       `-- export-preview.js
|-- config/database.php
|-- db/
|   |-- db.php
|   `-- sql/
|-- views/
|   |-- layout.php
|   |-- annonces/
|   |-- biens/
|   |-- export/
|   |-- errors/
|   `-- partials/
`-- src/
    |-- autoload.php
    |-- Config/
    |-- Controller/
    |-- Http/
    |-- Model/
    `-- View/
```

Toutes les classes applicatives sont sous le namespace `App\`.
L'autoloader de `src/autoload.php` mappe `App\` vers `src/`.

## Flow HTTP

`index.php` est le front controller. Toutes les requêtes web passent par lui.

1. `index.php` charge l'autoloader.
2. Il instancie la base, les repositories, les services, les presenters, le renderer et les controllers.
3. Il crée une `Request` avec `Request::fromGlobals()`.
4. Il lit `?action=...`, avec `index` par défaut.
5. Il choisit le controller :
   - `ExportController` pour `?action=export` ou `?export=...`
   - `BienController` pour les actions de biens libres
   - `AnnonceController` pour le catalogue et le CRUD d'annonces
6. Le controller retourne une `Response`.
7. `Response::send()` envoie le statut, les headers, puis le contenu.

`Request` encapsule `$_GET`, `$_POST` et `$_SERVER`.
`Response` encapsule le corps, le code HTTP et les headers.

## Flow Controllers

Les controllers font le routage applicatif avec `dispatch(Request $request)`.

`AnnonceController` gère :

- `GET ?action=index` : liste des annonces
- `GET ?action=show&id=...` : détail
- `GET ?action=create` : formulaire de création
- `POST ?action=store` : création
- `GET ?action=edit&id=...` : formulaire d'édition
- `POST ?action=update&id=...` : mise à jour
- `POST ?action=delete&id=...` : suppression

`BienController` gère les biens non liés à une annonce :

- `GET ?action=biens`
- `GET ?action=bien_create`
- `POST ?action=bien_store`

`ExportController` gère la page d'export, les filtres et les téléchargements JSON / CSV.

Les requêtes de modale utilisent `fragment=1` ou le header `X-Requested-With: fetch`.
Dans ce cas, le controller rend seulement le fragment HTML sans `layout.php`.
Les POST de modale renvoient du JSON contenant une redirection.

## Flow Views

Les vues sont rendues par `App\View\ViewRenderer`.

1. Le controller appelle `$this->views->render('annonces/index', $params)`.
2. `ViewRenderer` inclut `views/annonces/index.php` dans un buffer.
3. Le HTML obtenu est stocké dans `$content`.
4. `ViewRenderer` inclut ensuite `views/layout.php`.
5. Le layout affiche le contenu avec `<?= $content ?>`.

Les vues ne contiennent pas de logique métier. Elles affichent des tableaux préparés par les presenters :

- `AnnoncePresenter` transforme une `Annonce` en données affichables.
- `BienPresenter` transforme un `Bien` en données affichables.
- `CataloguePresenter` transforme une liste d'annonces.

Les partials dans `views/partials/` factorisent les composants réutilisés, par exemple les cartes d'annonces et le multi-select à badges.

## Model

Le domaine est dans `src/Model/`.

- `Annonce`, `AnnonceVente`, `AnnonceLocation` représentent les annonces.
- `Bien`, `Appartement`, `Maison`, `Villa` représentent les biens.
- `CategorieBien`, `TypeBien`, `Ville`, `EtatAnnonce` sont des enums.
- `PdoAnnonceRepository` et `PdoBienRepository` gèrent la persistance MySQL.
- `AnnonceFactory` et `BienFactory` hydratent les objets depuis les rows SQL.
- Les validators normalisent et valident les formulaires.
- Les exporters produisent les fichiers JSON / CSV.

Le schéma SQL utilise une logique proche du Single Table Inheritance :

- `biens` contient les colonnes communes et les colonnes spécifiques aux catégories.
- `annonces` contient les colonnes communes et les colonnes spécifiques aux ventes ou locations.
- Une annonce est liée à un seul bien via `annonces.bien_id`.

## Design Patterns

### MVC

Le code est séparé en trois zones :

- Model : métier, validation, persistance, exports
- View : templates, layout, partials, presenters
- Controller : routage applicatif et orchestration

### Front Controller

`index.php` est l'unique point d'entrée HTTP.
Il compose les dépendances au bord de l'application puis délègue au bon controller.

### Repository

`AnnonceRepository` et `BienRepository` définissent les contrats d'accès aux données.
Les controllers dépendent de ces contrats, tandis que `PdoAnnonceRepository` et `PdoBienRepository` contiennent le SQL.

### Factory

`AnnonceFactory` et `BienFactory` centralisent la création des objets métier depuis les lignes SQL.
Le repository récupère des données brutes, puis délègue l'hydratation aux factories.

### Singleton

`Database::getInstance()` garde une seule connexion PDO partagée par les repositories.
`Database::bootstrap()` sert au script `db/db.php`, qui doit pouvoir créer la base avant de s'y connecter.

### Observer

`PrixService`, `LoyerService` et `EtatService` notifient des observers quand une valeur change.
Les observers actuels écrivent dans `logs/prix.log`, `logs/loyer.log` et `logs/etat.log`.

### Strategy

Le pattern Strategy est utilisé pour :

- les exports, via l'interface `Exporter`
- la journalisation, via l'interface `LoggerStrategy`

Cela permet de changer le comportement concret sans modifier le code appelant.

## Export

La page `?action=export` permet d'exporter :

- les annonces, filtrées par catégorie, type, transaction, ville, état et prix max
- les biens sans annonce, filtrés par catégorie, type, ville et surface max

Les exports directs d'annonces restent disponibles :

| Format | URL            |
| ------ | -------------- |
| JSON   | `?export=json` |
| CSV    | `?export=csv`  |

Les exports de biens passent par :

- `?action=export&target=biens&download=1&format=json`
- `?action=export&target=biens&download=1&format=csv`

Le CSV utilise `;` comme séparateur et ajoute un BOM UTF-8 pour Excel.
