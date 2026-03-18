<?php

namespace PixellWeb\Rentiles\app\Console\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\EntryNotFoundException;

use PixellWeb\Rentiles\app\Data\ReservationData;
use PixellWeb\Rentiles\app\Ressources\Categorie;
use PixellWeb\Rentiles\app\Ressources\Lieu;
use PixellWeb\Rentiles\app\Ressources\Reservation;


class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentiles:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test rentiles';

    protected $browser;


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

        /*$lieu = new Lieu();
        dd($lieu->all());

        $categorie = new Categorie();
        dd($categorie->all());




        $reservation_data = ReservationData::validateAndCreate([
            'reference' => 'ggg',
            'categorie' => 'ggg',
            'statut' => 'Payé',
            'date_depart' => Carbon::now()->addDays(10),
            'date_retour' => Carbon::now()->addDays(11),
            'infosup' => 'ggg',
            'nom' => 'Doe',
            'prenom' => 'joh,',
            'telephone' => '0600000000',
            'email' => 'john@example.com',
            'lieu_depart' => 'll',
            'lieu_retour' => 'll',
            'montant' => 0,
            'date' =>  Carbon::now(),
        ]);

        $reservation->create($reservation_data);*/


        //dd($reservation->find('A008054'));
        $reservation = new Reservation();
        dump($reservation->nonTermine());
        foreach ($reservation->nonTermine() as $resa) {
            try {
                $reservation->find($resa);
            } catch (\Exception $exception) {
                dump($exception->getMessage());
            }

        }

        dd('fin');


        /*$this->browser = new HttpBrowser(HttpClient::create());

        $this->browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/index.php');
        $this->browser->submitForm('valider', [
            'identifiant' => config('rentiles.identifiant'),
            'motdepasse' => config('rentiles.password'),
        ]);


        $crawler = $this->browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/planningbo_ajax.php?action=gen_new_tab&from=06-03-2026&to=05-04-2026&delai_reprisestock_display=3&id_resa=0&id_exception=0&display_vehicule=0&nocache=1772836837508');
        $crawler->filter('#afflisting')->each(function (Crawler $reservation_element) {
            dd($reservation_element);
        });


        // Version listing des réservations
        $crawler = $this->browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/commande.php');
        $crawler->filter("#resul ul")->each(function (Crawler $reservation_element) {
            $link = $reservation_element->filter("a")->eq(0);
            $url = $link->attr("href");
            $reference = $link->text();
            $this->info($url.' '.$reference);

            $this->reservation($reference);
        });*/

    }


    protected function reservation(string $reference)
    {
        $crawler = $this->browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/commande_details.php?planning=1&ref='.$reference);

        $categorie = $crawler->filter('input[name="editcmd_vehicule[ref]"]')->first()->attr('value');
        $montant = $crawler->filter('.display_totcmd b')->first()->text();
        $date = $crawler->filter('#bloc_description > .ligne_claire_BlocDescription:not(.editcmd) li:not(.designation)')->first()->text();
        $prenom = $crawler->filter('#display_editcmd_prenom')->first()->text();
        $nom = $crawler->filter('#display_editcmd_nom')->first()->text();
        $lieu_depart = $crawler->filter('#bloc_photos .bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->first()->filter('li')->eq(1)->filter('span')->first()->text();
        $lieu_retour = $crawler->filter('.bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->eq(1)->filter('li')->eq(1)->filter('span')->first()->text();
        $date_depart = $crawler->filter('#lead span')->eq(1)->text();
        $heure_depart = $crawler->filter('#display_editcmd_heured')->first()->text();

        $data = compact('categorie', 'montant', 'date', 'prenom', 'nom', 'lieu_depart', 'lieu_retour', 'date_depart', 'heure_depart');
        $this->info(json_encode($data));
    }



}
