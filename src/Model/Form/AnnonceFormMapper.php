<?php

declare(strict_types=1);

namespace App\Model\Form;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\AnnonceLocation;
use App\Model\Annonce\AnnonceVente;
use App\Model\Bien\Appartement;
use App\Model\Bien\Maison;

/* *
 * Mapper pour les formulaires d'annonces immobilières, permettant de convertir
 * des objets Annonce en données de formulaire et vice versa, en gérant les
 * différentes propriétés des annonces et de leurs biens associés en fonction du
 * type de bien et du type d'annonce (vente ou location). Ce mapper utilise un
 * DecimalParser pour formater les valeurs numériques et un AnnonceFormValidator
 * pour fournir les données par défaut du formulaire.
 */
final class AnnonceFormMapper
{
    public function __construct(
        private readonly DecimalParser $decimals,
        private readonly AnnonceFormValidator $validator,
    ) {
    }

    public function formDataFromAnnonce(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        $data = [
            ...$this->validator->defaultFormData(),
            'titre'       => $annonce->getTitre() ?? '',
            'description' => $annonce->getDescription() ?? '',
            'categorie'   => $bien->getCategorie()->value,
            'type'        => $bien->getType()?->value ?? '',
            'ville'       => $bien->getVille(),
            'surface'     => $this->decimals->format((string) $bien->getSurface()),
            'chambres'    => (string) $bien->getChambres(),
            'transaction' => $annonce->getTypeTransaction(),
            'etat'        => $annonce->getEtat()->value,
        ];

        if ($bien instanceof Appartement) {
            $data['etage'] = (string) $bien->getEtage();
        }
        if ($bien instanceof Maison) {
            $data['terrain'] = $this->decimals->format((string) $bien->getTerrain());
        }

        if ($annonce instanceof AnnonceVente) {
            $data['prix_initial'] = $this->decimals->format($annonce->getPrixInitial());
            $data['prix_courant'] = $this->decimals->format($annonce->getPrixCourant());
        }

        if ($annonce instanceof AnnonceLocation) {
            $data['loyer_initial']     = $this->decimals->format($annonce->getLoyerInitial());
            $data['loyer']             = $this->decimals->format($annonce->getLoyer());
            $data['charges_initiales'] = $this->decimals->format($annonce->getChargesInitiales());
            $data['charges']           = $this->decimals->format($annonce->getCharges());
        }

        return $data;
    }

    public function applyLockedPost(Annonce $annonce, array $post): array
    {
        $bien = $annonce->getBien();
        $post['categorie']   = $bien->getCategorie()->value;
        $post['type']        = $bien->getType()?->value ?? '';
        $post['ville']       = $bien->getVille();
        $post['surface']     = $this->decimals->format((string) $bien->getSurface());
        $post['chambres']    = (string) $bien->getChambres();
        $post['transaction'] = $annonce->getTypeTransaction();

        if ($bien instanceof Appartement) {
            $post['etage'] = (string) $bien->getEtage();
        }
        if ($bien instanceof Maison) {
            $post['terrain'] = $this->decimals->format((string) $bien->getTerrain());
        }

        if ($annonce instanceof AnnonceVente) {
            $post['prix_initial'] = $this->decimals->format($annonce->getPrixInitial());
        }
        if ($annonce instanceof AnnonceLocation) {
            $post['loyer_initial']     = $this->decimals->format($annonce->getLoyerInitial());
            $post['charges_initiales'] = $this->decimals->format($annonce->getChargesInitiales());
        }

        return $post;
    }

    public function forceLockedFields(Annonce $annonce, array $data): array
    {
        $bien = $annonce->getBien();

        $data['categorie']   = $bien->getCategorie()->value;
        $data['type']        = $bien->getType()?->value;
        $data['ville']       = $bien->getVille();
        $data['surface']     = (string) $bien->getSurface();
        $data['chambres']    = $bien->getChambres();
        $data['etage']       = $bien instanceof Appartement ? $bien->getEtage() : null;
        $data['terrain']     = $bien instanceof Maison ? (string) $bien->getTerrain() : null;
        $data['transaction'] = $annonce->getTypeTransaction();

        if ($annonce instanceof AnnonceVente) {
            $data['prix_initial']      = $annonce->getPrixInitial();
            $data['loyer_initial']     = null;
            $data['loyer']             = null;
            $data['charges_initiales'] = null;
            $data['charges']           = null;
        }

        if ($annonce instanceof AnnonceLocation) {
            $data['loyer_initial']     = $annonce->getLoyerInitial();
            $data['charges_initiales'] = $annonce->getChargesInitiales();
            $data['prix_initial']      = null;
            $data['prix_courant']      = null;
        }

        return $data;
    }
}
