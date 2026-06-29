<?php

namespace PixellWeb\Rentiles;


use Ipsum\Reservation\app\Events\ReservationConfirmedEvent;
use PixellWeb\Rentiles\app\Events\ReservationCreateFrontEvent;
use PixellWeb\Rentiles\app\Listeners\SendReservation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use PixellWeb\Rentiles\app\Listeners\SendReservationNotificationCatch;
use PixellWeb\Rentiles\app\Notifications\RentilesImport;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ReservationConfirmedEvent::class => [
            SendReservationNotificationCatch::class
        ],
        ReservationCreateFrontEvent::class => [
            SendReservation::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
