<?php

namespace PixellWeb\Rentiles\app\Ressources;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Exception\GuzzleException;
use PixellWeb\Rentiles\app\RentilesException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Validator;

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
        // Récupération des réfèrences. Pas de possibilité de selectionner via css
        preg_match_all('/<p [^>]*background-color:#FF0F02;[^>]*>.*?planning=1&ref=([^"]*)">/s', $result, $output);

        return array_unique($output[1]);
    }


    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function find(string $reference): array
    {
        $result = $this->request->get('commande_details.php', [
            'ref' => $reference,
            'planning' => 1 // Charge moins de code html inutile
        ]);

        $data = [
            'reference' => $reference,
        ];

        $crawler = new Crawler($result);
        $data['categorie'] = $crawler->filter('input[name="editcmd_vehicule[ref]"]')->first()->attr('value');


        $data['statut'] = $crawler->filter('#statutch option[selected]')->first()->text();

        $i = 1;
        $data['options'] = [];
        while ($crawler->filter('input[name="editcmd_options['.$i.'][ref]"]')->count()) {
            $data['options'][] = [
                'reference' => $crawler->filter('input[name="editcmd_options['.$i.'][ref]"]')->first()->attr('value'),
                'quantite' => $crawler->filter('input[name="editcmd_options['.$i.'][qte]"]')->first()->attr('value'),
                'total' => $crawler->filter('input[name="editcmd_options['.$i.'][prixuqte]"]')->first()->attr('value'),
            ];
            $i++;
        }

        $montant = $crawler->filter('.display_totcmd b')->first()->text();
        $data['montant'] = str_replace(' €', '', $montant);
        $data['date'] = $crawler->filter('#bloc_description > .ligne_claire_BlocDescription:not(.editcmd) li:not(.designation)')->first()->text();


        $data['prenom'] = $crawler->filter('#display_editcmd_prenom')->first()->text();
        $data['nom'] = $crawler->filter('#display_editcmd_nom')->first()->text();
        $adresse = $crawler->filter('#display_editcmd_adresse1')->first()->text().' '.$crawler->filter('#display_editcmd_adresse2')->first()->text();
        $data['adresse'] = trim($adresse);
        $data['code_postal'] = $crawler->filter('#display_editcmd_cpostal')->first()->text();
        $data['ville'] = $crawler->filter('#display_editcmd_ville')->first()->text();
        $data['pays'] = $crawler->filter('#display_editcmd_pays')->first()->text();
        $data['telephone'] = $crawler->filter('#display_editcmd_tel')->first()->text();
        $data['email'] = $crawler->filter('#display_editcmd_email')->first()->text();

        $data['permis_numero'] = $crawler->filter('#display_editcmd_permis')->first()->text();
        $data['permis_lieu'] = $crawler->filter('#editcmd_lieu_permis')->first()->text();
        $data['permis_date'] = $crawler->filter('#display_editcmd_date_permis')->first()->text();
        $data['date_naissance'] = $crawler->filter('#display_editcmd_date_naissance')->first()->text();
        $data['lieu_naissance'] = $crawler->filter('#editcmd_lieu_naissance')->first()->text();
        $data['franchise'] = $crawler->filter('#display_editcmd_franchise')->first()->text();
        $data['caution'] = $crawler->filter('#display_editcmd_caution')->first()->text();


        $data['lieu_depart'] = $crawler->filter('#bloc_photos .bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->first()->filter('li')->eq(1)->filter('span')->first()->text();
        $data['lieu_retour'] = $crawler->filter('.bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->eq(1)->filter('li')->eq(1)->filter('span')->first()->text();
        $data['date_depart'] = $crawler->filter('#lead span')->eq(1)->text().' '.$crawler->filter('#display_editcmd_heured')->first()->text();
        $data['date_retour'] = $crawler->filter('#leaf span')->eq(1)->text().' '.$crawler->filter('#display_editcmd_heuref')->first()->text();
        $data['infosup'] = $crawler->filter('#display_editcmd_infosup')->first()->text();


        $validator = Validator::make($data, $this->validation());
        if ($validator->fails()) {
            dump($data, $validator->errors()->all());
            throw new RentilesException(print_r($validator->errors()->all(), true)); // TODO format
        }

        return $data;
    }



    protected function validation()
    {
        return [
            'categorie' => 'required',
            'montant' => 'required|numeric',
            'date' => 'required|date_format:d/m/y H:i:s',

            'options' => 'array',
            'options.*.reference' => 'required',
            'options.*.quantite' => 'required|numeric',
            'options.*.total' => 'required|numeric',

            'prenom' => 'required',
            'nom' => 'required',
            'email' => 'required|email',

            'permis_date' => 'nullable|date_format:d/m/Y',
            'date_naissance' => 'nullable|date_format:d/m/Y',

            'franchise' => 'nullable|numeric',
            'caution' => 'nullable|numeric',

            'lieu_depart' => 'required',
            'lieu_retour' => 'required',
            'date_depart' => 'required|date_format:d/m/Y H:i',
            'date_retour' => 'required|date_format:d/m/Y H:i',
        ];
    }


}
