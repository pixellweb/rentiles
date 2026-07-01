<?php

namespace PixellWeb\Rentiles\app\Http\Controllers;


use Alert;
use Ipsum\Admin\app\Http\Controllers\AdminController;
use Ipsum\Reservation\app\Models\Reservation\Reservation;
use PixellWeb\Rentiles\app\Events\ReservationCreateFrontEvent;
use PixellWeb\Rentiles\app\RentilesException;

class ReservationController extends AdminController
{


    public function rentiles(Reservation $reservation)
    {

        try {

            ReservationCreateFrontEvent::dispatch($reservation);

        } catch (RentilesException $e) {
            $message = print_r($e->getMessage(), true);
            Alert::error("La réservation n'a pas été envoyée à Rentîles : $message")->flash();
            return back();
        }

        Alert::success("La réservation a bien été envoyée à Rentîles")->flash();
        return back();
    }
}
