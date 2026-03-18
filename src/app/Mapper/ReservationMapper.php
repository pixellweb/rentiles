<?php

namespace PixellWeb\Rentiles\app\Mapper;

use Ipsum\Reservation\app\Models\Reservation\Condition;
use Ipsum\Reservation\app\Models\Reservation\Etat;
use Ipsum\Reservation\app\Models\Reservation\Reservation As IpsumReservation;
use PixellWeb\Rentiles\app\Data\ReservationData;
use PixellWeb\Rentiles\app\RentilesException;

class ReservationMapper
{
    public function __construct(
        public array $categories_mapping,
        public array $lieux_mapping,
        public array $prestations_mapping,
    ) {
        // TODO créer une class Mapping avec les requetes categorie, lieu et prestation ?
    }

    public function create(ReservationData $reservation_data): IpsumReservation|bool
    {
        if (!isset($this->categories_mapping[$reservation_data->categorie->reference])) {
            return false;
        }
        if (!isset($this->lieux_mapping[$reservation_data->lieu_depart->nom])
            or !isset($this->lieux_mapping[$reservation_data->lieu_retour->nom])) {
            throw new RentilesException('erreur de mapping de lieu'); // plutôt mettre un commentaire ?
        }
        // TODO option prestation

        $data = [
            'reference' => $reservation_data->reference,
            'etat_id' => Etat::VALIDEE_ID,
            'condition_paiement_id' => Condition::LIGNE_ID,
            'source_id' => config('rentiles.source_id'),
            'nom' => $reservation_data->nom,
            'prenom' => $reservation_data->prenom,
            'email' => $reservation_data->email,
            'telephone' => $reservation_data->telephone,
            'adresse' => $reservation_data->adresse,
            'cp' => $reservation_data->code_postal,
            'ville' => $reservation_data->ville,
            'pays_nom' => $reservation_data->pays,
            'naissance_at' => $reservation_data->date_naissance,
            'naissance_lieu' => $reservation_data->lieu_naissance,
            'permis_numero' => $reservation_data->permis_numero,
            'permis_at' => $reservation_data->permis_date,
            'permis_delivre' => $reservation_data->permis_lieu,
            'observation' => $reservation_data->infosup,
            'categorie_id' => $this->categories_mapping[$reservation_data->categorie->reference],
            'categorie_nom' => $reservation_data->categorie->titre,
            'caution' => $reservation_data->caution,
            'franchise' => $reservation_data->franchise,
            'debut_at' => $reservation_data->date_depart,
            'fin_at' => $reservation_data->date_retour,
            'debut_lieu_id' => $this->lieux_mapping[$reservation_data->lieu_depart->nom],
            'fin_lieu_id' => $this->lieux_mapping[$reservation_data->lieu_retour->nom],
            'debut_lieu_nom' => $reservation_data->lieu_depart->nom,
            'fin_lieu_nom' => $reservation_data->lieu_retour->nom,
            'prestations ' => null, // TODO
            'total' => $reservation_data->montant,
            'montant_paye' => $reservation_data->montant_paye, // TODO supprimer il faut faire le paiement
            'saved' => 1,
            'created_at' => $reservation_data->date,
        ];
        return IpsumReservation::create($data);
    }

    public function get(IpsumReservation $ipsum_reservation): ReservationData
    {
        // TODO
        return ReservationData::from($ipsum_reservation->toArray());
    }
}


