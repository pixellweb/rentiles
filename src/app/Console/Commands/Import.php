<?php

namespace PixellWeb\Rentiles\app\Console\Commands;


use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Container\EntryNotFoundException;

use Ipsum\Reservation\app\Location\Categorie;
use Ipsum\Reservation\app\Models\Lieu\Lieu;
use Ipsum\Reservation\app\Models\Prestation\Prestation;
use Ipsum\Reservation\app\Models\Reservation\Reservation as IpsumReservation;
use PixellWeb\Rentiles\app\Mapper\ReservationMapper;
use PixellWeb\Rentiles\app\RentilesException;
use PixellWeb\Rentiles\app\Ressources\Reservation as ReservationRessource;
use Psr\SimpleCache\InvalidArgumentException;


class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentiles:import';

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
     * @return void
     * @throws EntryNotFoundException
     */
    public function handle(): void
    {



        // Création des nouvelles réservations
        // A lancer régulièrement
        $reservation_data = new ReservationRessource();

        try {
            $reservations_reference = $reservation_data->nonTermine();

            if (count($reservations_reference)) {

                $references_exist = IpsumReservation::select('reference')->whereIn('reference', $reservations_reference)->get();
                $reservations_a_creer = $reservations_reference->diff($references_exist);
                if ($reservations_a_creer->count()) {

                    $categories = Categorie::select(['id', 'custom_fields'])
                        ->get()
                        ->mapWithKeys(function ($item, $key) {
                        return [$key => $item->custom_fields->rentiles_code];
                    });

                    $lieux = Lieu::select(['id', 'custom_fields'])
                        ->get()
                        ->mapWithKeys(function ($item, $key) {
                            return [$key => $item->custom_fields->rentiles_code];
                        });

                    $prestations = Prestation::select(['id', 'custom_fields'])
                        ->get()
                        ->mapWithKeys(function ($item, $key) {
                            return [$key => $item->custom_fields->rentiles_code];
                        });

                    $reservation_mapper = new ReservationMapper($categories->toArray(), $lieux->toArray(), $prestations->toArray());

                    foreach ($reservations_a_creer as $reference) {
                        try {
                            $rentiles_reservations = $reservation_data->find($reference);
                            $reservation_mapper->create($rentiles_reservations);
                        } catch (\Exception $exception) {
                            dump($exception->getMessage());
                        }
                        dd('stop');
                    }
                }

            }
        } catch (GuzzleException $e) {

        } catch (RentilesException $e) {

        } catch (InvalidArgumentException $e) {

        }



    }


}
