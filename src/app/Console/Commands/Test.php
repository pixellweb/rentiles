<?php

namespace PixellWeb\Rentiles\app\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Container\EntryNotFoundException;
use Ipsum\Admin\app\Classes\LogViewer;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

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



        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/index.php');
        $browser->submitForm('valider', [
            'identifiant' => '',
            'motdepasse' => '',
        ]);

        $crawler = $browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/commande.php');

        $crawler->filter("#resul ul")->each(function (Crawler $reservation_element) use ($browser) {
            $link = $reservation_element->filter("a")->eq(0);
            $url = $link->attr("href");
            $reference = $link->text();
            $this->info($url.' '.$reference);

            $crawler = $browser->request('GET', 'https://ww6.speedyrent.fr/srr-twinandgo/admin_twinandgo/'.$url);

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
        });

    }



}
