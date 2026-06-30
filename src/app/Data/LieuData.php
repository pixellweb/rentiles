<?php

namespace PixellWeb\Rentiles\app\Data;

use Spatie\LaravelData\Data;


class LieuData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nom,

    ) {
    }
}
