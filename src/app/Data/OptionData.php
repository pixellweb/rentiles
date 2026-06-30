<?php

namespace PixellWeb\Rentiles\app\Data;

use Spatie\LaravelData\Data;


class OptionData extends Data
{
    public function __construct(
        public string $reference,
        public int $quantite,
        public float $total,

    ) {
    }
}
