<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ipsum\Reservation\app\Events\ReservationUpdatedEvent;
use PixellWeb\Rentiles\app\Mapper\ReservationMapper;
use PixellWeb\Rentiles\app\RentilesException;
use PixellWeb\Rentiles\app\Ressources\Reservation;
use Alert;

class UpdateReservation
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


    public function handle(ReservationUpdatedEvent $event)
    {
        if (!$event->reservation->custom_fields->rentiles_reference) {
            return null;
        }

        try {
            $reservation_mapper = new ReservationMapper();

            $reservation = new Reservation();
            $reservation->update($reservation_mapper->get($event->reservation), $event->reservation->etat_id);

        } catch (RentilesException $e) {
            $message = print_r($e->getMessage(), true);
            Alert::error("La réservation n'a pas été mise à jour sur Rentîles : $message")->flash();
            return back();
        }

    }
}
