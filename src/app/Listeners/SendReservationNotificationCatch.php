<?php

namespace PixellWeb\Rentiles\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Ipsum\Reservation\app\Events\ReservationConfirmedEvent;
use Alert;
use PixellWeb\Rentiles\app\Events\ReservationCreateFrontEvent;
use PixellWeb\Rentiles\app\RentilesException;

class SendReservationNotificationCatch
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

        try {

            ReservationCreateFrontEvent::dispatch($event->reservation);

        } catch (RentilesException $e) {
            $message = print_r($e->getMessage(), true);
            Alert::error("La réservation n'a pas été envoyée à Rentîles : $message")->flash();
            return back();
        }
    }
}
