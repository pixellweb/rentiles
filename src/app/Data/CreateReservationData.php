<?php

namespace PixellWeb\Rentiles\app\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;


class CreateReservationData extends Data
{

    public function __construct(
        public ?string $reference,

        public int $categorie_id,
        public Carbon $date_depart,
        public Carbon $date_retour,
        public ?string $infosup,

        public ?string $prenom,
        public string $nom,
        public ?string $telephone,
        public ?string $email,

        public ?int $montant,

        public Carbon $date,
        public string $lieu_depart,
        public string $lieu_retour,



    ) {
    }
}
