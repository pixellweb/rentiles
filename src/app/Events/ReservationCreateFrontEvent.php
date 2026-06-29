<?php

namespace PixellWeb\Rentiles\app\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ipsum\Reservation\app\Models\Reservation\Reservation;

class ReservationCreateFrontEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Reservation $reservation)
    {
        //
    }
}
