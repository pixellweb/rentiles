<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ipsum\Reservation\app\Events\ReservationConfirmedEvent;

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
        $event->reservation;
    }
}
