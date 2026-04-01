<?php

namespace PixellWeb\Rentiles\app\Console\Commands;


use Carbon\Carbon;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Ipsum\Reservation\app\Models\Reservation\Reservation as IpsumReservation;
use PixellWeb\Rentiles\app\Mapper\ReservationMapper;
use PixellWeb\Rentiles\app\Notifications\RentilesImport;
use PixellWeb\Rentiles\app\Ressources\Reservation as ReservationRessource;
use Symfony\Component\Console\Command\Command as CommandAlias;


class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentiles:import {action=all} {--cache=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import rentiles';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws EntryNotFoundException
     */
    public function handle(): mixed
    {
        $errors = collect();

        try {
            $reservation_data = new ReservationRessource($this->option('cache'));

            $this->info('Récupération des réservations non terminées');

            if ($this->argument('action') == 'all') {
                $reservations_reference = $reservation_data->nonTermine(Carbon::now()->addDay());

                if (!count($reservations_reference)) {
                    return CommandAlias::SUCCESS;
                }

                $reservations_a_creer = $reservations_reference;
            } elseif ($this->argument('action') == 'new') {
                $reservations_reference = $reservation_data->nonTermine();

                if (!count($reservations_reference)) {
                    return CommandAlias::SUCCESS;
                }

                $references_exist = IpsumReservation::select('reference')->whereIn('reference', $reservations_reference)->get();
                $reservations_a_creer = $reservations_reference->diff($references_exist->pluck('reference'));
            } else {
                $this->error('Argument action inconnu');
                return CommandAlias::INVALID;
            }

            $this->info($reservations_a_creer->count().' réservations à créer ou modifier');

            $reservation_mapper = new ReservationMapper();

            foreach ($reservations_a_creer as $reference) {
                $this->info('Création réservation '.$reference);
                try {
                    $rentiles_reservations = $reservation_data->find($reference);
                    $reservation_mapper->updateOrCreate($rentiles_reservations);
                } catch (\Exception $exception) {
                    $errors->push($exception->getMessage());
                    $this->error($exception->getMessage());
                }
            }


        } catch (\Exception $exception) {
            $errors->push($exception->getMessage());
            $this->error($exception->getMessage());
        }

        if ($errors->isNotEmpty()) {
            Notification::route('mail', 'taylor@example.com')->notify(new RentilesImport($errors));
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }


}
