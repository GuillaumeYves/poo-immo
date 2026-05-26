# poo-immo

Exercice IT-Akademy : application PHP orientée objet d'un catalogue d'annonces immobilières pour s'entrainer sur les pratiques POO

## Lancement

Depuis la racine du projet :

```bash
php -S localhost:8000
```

Puis ouvrir [http://localhost:8000](http://localhost:8000).

## Structure du projet

```
poo-immo/
├── index.php                       # Point d'entrée : seed du catalogue, rendu HTML, routage export
└── src/
    ├── Entity/
    │   ├── BienImmo.php            # Classe abstraite
    │   ├── Appartement.php         # BienImmo + type de bien appartement
    │   ├── Maison.php              # BienImmo + type de bien maison
    │   ├── Annonce.php             # Classe abstraite
    │   ├── AnnonceVente.php        # Annonce + type d'annonce vente
    │   └── AnnonceLocation.php     # Annonce + type d'annonce location
    ├── Repository/
    │   └── AnnonceRepository.php   # Collection en mémoire + recherches (ville, transaction, type)
    ├── Presenter/
    │   └── BienPresenter.php       # Transforme les entités en données prêtes pour la vue
    └── Exporter/
        ├── ExporterInterface.php   # Contrat d'export
        ├── AnnonceArrayConverter.php  # Conversion Annonce → tableau plat (mutualisé)
        ├── JsonExporter.php
        └── CsvExporter.php
```

## Modèle objet

- `BienImmo` (abstraite) ⟵ `Appartement`, `Maison`
- `Annonce` (abstraite) ⟵ `AnnonceVente`, `AnnonceLocation`
- Une `Annonce` **a un** `BienImmo`.

## Export du catalogue

Deux formats disponibles, accessibles depuis les boutons en haut de la page ou directement via URL :

| Format | URL              | Fichier généré             |
| ------ | ---------------- | ----------------------------- |
| JSON   | `?export=json` | `catalogue-AAAA-MM-JJ.json` |
| CSV    | `?export=csv`  | `catalogue-AAAA-MM-JJ.csv`  |

Le CSV utilise `;` comme séparateur et un BOM UTF-8 (compatible Excel en français).
