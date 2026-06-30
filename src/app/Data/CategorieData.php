<?php

namespace PixellWeb\Rentiles\app\Data;

use Spatie\LaravelData\Data;


class CategorieData extends Data
{
    public function __construct(
        public ?int $id,
        public string $reference,
        public string $titre,

    ) {
    }
}
