<?php

namespace PixellWeb\Rentiles\app\Data;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PixellWeb\Rentiles\app\Enum\Statut;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;


class CreateReservationData extends Data
{

    public function __construct(
        public int $categorie,
        public Carbon $date_depart,
        public Carbon $date_retour,
        public ?string $infosup,

        public ?string $prenom,
        public string $nom,
        public ?string $telephone,
        public ?string $email,

        public ?int $montant,

        public Carbon $date,
        public int $lieu_depart,
        public int $lieu_retour,



    ) {
    }
}
