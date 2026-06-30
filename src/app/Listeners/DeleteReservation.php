<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ipsum\Reservation\app\Events\ReservationDeletedEvent;
use PixellWeb\Rentiles\app\RentilesException;
use PixellWeb\Rentiles\app\Ressources\Reservation;
use Alert;

class DeleteReservation
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


    public function handle(ReservationDeletedEvent $event)
    {

        if (!$event->reservation->custom_fields->rentiles_reference) {
            return null;
        }

        try {

            $reservation = new Reservation();
            $reservation->delete($event->reservation->custom_fields->rentiles_reference);

        } catch (RentilesException $e) {
            $message = print_r($e->getMessage(), true);
            Alert::error("La réservation n'a pas été supprimée sur Rentîles : $message")->flash();
            return back();
        }

    }
}
