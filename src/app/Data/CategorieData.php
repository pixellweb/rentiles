<?php

namespace PixellWeb\Rentiles\app\Data;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PixellWeb\Rentiles\app\Enum\Statut;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Ipsum\Reservation\app\Models\Reservation as IpsumReservation;


class CategorieData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $nom,

    ) {
    }
}
