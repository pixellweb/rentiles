<?php

use Illuminate\Support\Facades\Route;

Route::controller(\PixellWeb\Rentiles\app\Http\Controllers\ReservationController::class)->prefix('reservation')->name('admin.reservation.')->group(
    function () {
        Route::get('{reservation}/rentiles', 'rentiles')->name('rentiles');
    }
);
