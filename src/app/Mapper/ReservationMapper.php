<?php

namespace PixellWeb\Rentiles\app\Mapper;

use Ipsum\Reservation\app\Models\Reservation\Reservation As IpsumReservation;
use PixellWeb\Rentiles\app\Data\ReservationData;

class ReservationMapper
{
    public function __construct(
        public array $categories_mapping,
        public array $lieux_mapping,
        public array $prestations_mapping,
    ) {

    }

    public function create(ReservationData $reservation_data): IpsumReservation
    {
        // TODO
        return IpsumReservation::create($reservation_data->toArray());
    }

    public function get(IpsumReservation $ipsum_reservation): ReservationData
    {
        // TODO
        return ReservationData::from($ipsum_reservation->toArray());
    }
}


