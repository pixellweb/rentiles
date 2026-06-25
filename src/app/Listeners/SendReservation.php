<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ipsum\Reservation\app\Events\ReservationConfirmedEvent;
use PixellWeb\Rentiles\app\Mapper\ReservationMapper;
use PixellWeb\Rentiles\app\Ressources\Reservation;

class SendReservation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function handle(ReservationConfirmedEvent $event)
    {
        $reservation_mapper = new ReservationMapper();
        $reservation = new Reservation();
        $reservation->create($reservation_mapper->get($event->reservation));

    }
}
