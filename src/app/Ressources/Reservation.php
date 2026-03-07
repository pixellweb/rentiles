<?php

namespace PixellWeb\Rentiles\app\Ressources;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Exception\GuzzleException;
use PixellWeb\Rentiles\app\RentilesException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class Reservation extends Ressource
{


    /**
     * @desc Recherche dans le planning pour avoir que les résa futurs
     * @param CarbonInterface|null $debut
     * @param CarbonInterface|null $fin
     * @return array
     * @throws GuzzleException
     * @throws RentilesException
     * @throws InvalidArgumentException
     */
    public function nonTermine(CarbonInterface $debut = null, CarbonInterface $fin = null) :array
    {
        $debut = $debut ?? Carbon::now();
        $fin = $fin ?? $debut->clone()->addMonths(12);

        $result = $this->request->get('planningbo_ajax.php', [
            'action' => 'gen_new_tab',
            'from' => $debut->format('d-m-Y'),
            'to' => $fin->format('d-m-Y'),
            'delai_reprisestock_display' => 3,
            'id_resa' => 0,
            'id_exception' =>0,
            'display_vehicule' => 0,
            'nocache' => time()
        ]);
        // Pas de possibilité de selectionner via css
        preg_match_all('/<p [^>]*background-color:#FF0F02;[^>]*>.*?planning=1&ref=([^"]*)">/s', $result, $output);

        return array_unique($output[1]);
    }


    public function find(string $reference): array
    {
        $result = $this->request->get('commande_details.php', [
            'ref' => $reference,
            'planning' => 1 // Charge moins de code html inutile
        ]);


        $crawler = new Crawler($result);
        $categorie = $crawler->filter('input[name="editcmd_vehicule[ref]"]')->first()->attr('value');
        $montant = $crawler->filter('.display_totcmd b')->first()->text();
        $montant = str_replace('€', '.', $montant);

        $date = $crawler->filter('#bloc_description > .ligne_claire_BlocDescription:not(.editcmd) li:not(.designation)')->first()->text();
        $prenom = $crawler->filter('#display_editcmd_prenom')->first()->text();
        $nom = $crawler->filter('#display_editcmd_nom')->first()->text();
        $lieu_depart = $crawler->filter('#bloc_photos .bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->first()->filter('li')->eq(1)->filter('span')->first()->text();
        $lieu_retour = $crawler->filter('.bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->eq(1)->filter('li')->eq(1)->filter('span')->first()->text();
        $date_depart = $crawler->filter('#lead span')->eq(1)->text();
        $heure_depart = $crawler->filter('#display_editcmd_heured')->first()->text();

        return compact('categorie', 'montant', 'date', 'prenom', 'nom', 'lieu_depart', 'lieu_retour', 'date_depart', 'heure_depart');
    }

}
