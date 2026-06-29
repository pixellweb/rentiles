<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use PixellWeb\Rentiles\app\Events\ReservationCreateFrontEvent;
use PixellWeb\Rentiles\app\Mapper\ReservationMapper;
use PixellWeb\Rentiles\app\Ressources\Client;
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


    public function handle(ReservationCreateFrontEvent $event)
    {
        $client = new Client();
        $client_id = $client->create($event->reservation->nom, $event->reservation->prenom, $event->reservation->email);

        $reservation_mapper = new ReservationMapper();
        $reservation = new Reservation();
        $reference = $reservation->create($reservation_mapper->get($event->reservation), $client_id);

        $event->reservation->custom_fields->rentiles_reference = $reference;
        $event->reservation->save();

    }
}
