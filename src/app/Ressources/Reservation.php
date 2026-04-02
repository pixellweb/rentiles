<?php

namespace PixellWeb\Rentiles\app\Ressources;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Validation\ValidationException;
use PixellWeb\Rentiles\app\Data\CreateReservationData;
use PixellWeb\Rentiles\app\RentilesException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use PixellWeb\Rentiles\app\Data\ReservationData as ReservationData;

class Reservation extends Ressource
{


    /**
     * @desc Recherche dans le planning pour avoir que les résa futurs
     * @param CarbonInterface|null $debut
     * @param CarbonInterface|null $fin
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     * @throws RentilesException
     * @throws InvalidArgumentException
     */
    public function nonTermine(CarbonInterface $debut = null, CarbonInterface $fin = null) : \Illuminate\Support\Collection
    {
        $debut = $debut ?? Carbon::now();
        $fin = $fin ?? $debut->clone()->addMonths(12);

        $result = $this->crawler->get(config('rentiles.admin_path').'/planningbo_ajax.php', [
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

        return collect(array_unique($output[1]));
    }


    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function find(string $reference): ReservationData
    {
        $result = $this->crawler->get(config('rentiles.admin_path').'/commande_details.php', [
            'ref' => $reference,
            'planning' => 1 // Charge moins de code html inutile
        ]);;

        $data = [
            'reference' => $reference,
        ];

        $dom_crawler = new DomCrawler($result);
        $categorie['reference'] = $dom_crawler->filter('input[name="editcmd_vehicule[ref]"]')->first()->attr('value');
        $categorie['titre'] = $dom_crawler->filter('input[name="editcmd_vehicule[titre]"]')->first()->attr('value');
        $data['categorie'] = $categorie;

        $data['statut'] = $dom_crawler->filter('#statutch option[selected]')->first()->text();

        $i = 1;
        $data['options'] = [];
        while ($dom_crawler->filter('input[name="editcmd_options['.$i.'][ref]"]')->count()) {
            $data['options'][] = [
                'reference' => $dom_crawler->filter('input[name="editcmd_options['.$i.'][ref]"]')->first()->attr('value'),
                'quantite' => $dom_crawler->filter('input[name="editcmd_options['.$i.'][qte]"]')->first()->attr('value'),
                'total' => $dom_crawler->filter('input[name="editcmd_options['.$i.'][prixuqte]"]')->first()->attr('value'),
            ];
            $i++;
        }

        $montant = $dom_crawler->filter('.display_totcmd b')->first()->text();
        $data['montant'] = str_replace(' €', '', $montant);
        $data['date'] = $dom_crawler->filter('#bloc_description > .ligne_claire_BlocDescription:not(.editcmd) li:not(.designation)')->first()->text();


        $data['prenom'] = $dom_crawler->filter('#display_editcmd_prenom')->first()->text();
        $data['nom'] = $dom_crawler->filter('#display_editcmd_nom')->first()->text();
        $adresse = $dom_crawler->filter('#display_editcmd_adresse1')->first()->text().' '.$dom_crawler->filter('#display_editcmd_adresse2')->first()->text();
        $data['adresse'] = trim($adresse);
        $data['code_postal'] = $dom_crawler->filter('#display_editcmd_cpostal')->first()->text();
        $data['ville'] = $dom_crawler->filter('#display_editcmd_ville')->first()->text();
        $data['pays'] = $dom_crawler->filter('#display_editcmd_pays')->first()->text();
        $data['telephone'] = $dom_crawler->filter('#display_editcmd_tel')->first()->text();
        $data['email'] = $dom_crawler->filter('#display_editcmd_email')->first()->text();

        $data['permis_numero'] = $dom_crawler->filter('#display_editcmd_permis')->first()->text();
        $data['permis_lieu'] = $dom_crawler->filter('#display_editcmd_lieu_permis')->first()->text();
        $data['permis_date'] = $dom_crawler->filter('#display_editcmd_date_permis')->first()->text();
        $data['date_naissance'] = $dom_crawler->filter('#display_editcmd_date_naissance')->first()->text();
        $data['lieu_naissance'] = $dom_crawler->filter('#display_editcmd_lieu_naissance')->first()->text();
        $data['franchise'] = $dom_crawler->filter('#display_editcmd_franchise')->first()->text();
        $data['caution'] = $dom_crawler->filter('#display_editcmd_caution')->first()->text();


        //$data['lieu_depart']['nom'] = $dom_crawler->filter('#bloc_photos .bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->first()->filter('li')->eq(1)->filter('span')->first()->text();
        //$data['lieu_retour']['nom'] = $dom_crawler->filter('.bloc_transfert')->eq(1)->filter('ul:not(.editcmd)')->eq(1)->filter('li')->eq(1)->filter('span')->first()->text();
        $data['lieu_depart']['id'] = $dom_crawler->filter('#editcmd_lieud option[selected]')->first()->attr('value');
        $data['lieu_depart']['nom'] = $dom_crawler->filter('#editcmd_lieud option[selected]')->first()->text();
        $data['lieu_retour']['id'] = $dom_crawler->filter('#editcmd_lieuf option[selected]')->first()->attr('value');
        $data['lieu_retour']['nom'] = $dom_crawler->filter('#editcmd_lieuf option[selected]')->first()->text();
        $data['date_depart'] = $dom_crawler->filter('#lead span')->eq(1)->text().' '.$dom_crawler->filter('#display_editcmd_heured')->first()->text();
        $data['date_retour'] = $dom_crawler->filter('#leaf span')->eq(1)->text().' '.$dom_crawler->filter('#display_editcmd_heuref')->first()->text();
        $data['infosup'] = $dom_crawler->filter('#display_editcmd_infosup')->first()->text();


        $conducteur_additionnel = $dom_crawler->filter('#cont_ajout_conducteur .designation[style*="#ffd8d8"]')->each(function (DomCrawler $element) {
            return $element->text();
        });
        $data['conducteur_additionnel'] = collect($conducteur_additionnel);

        $data['adresse_sur_place'] = $dom_crawler->filter('#editcmd_adresse_residence')->first()->text();

        // Commentaire/observation c'est quoi ? a priori le commentaire du loueur ?
        $data['commentaire'] = $dom_crawler->filter('#editcmd_commentaires')->first()->text();

        $data = array_map(function($item) { return $item !== '' ? $item : null; }, $data);

        try {
            ReservationData::validate($data);
        } catch (ValidationException $exception) {
            throw new RentilesException($exception->getMessage());
        }

        return ReservationData::from($data);
    }


    public function create(CreateReservationData $reservation)
    {
        $result = $this->crawler->get('module-resa/module_resa.inc.php', [
            'ajax' => 1,
            'action' => 'formresasubmit',
            'admin_tpl' => 'commandecreer',
            'id_categorie' => 1, // C'est quoi ?
            'typetarif' => 'internet',
            'date_d' => $reservation->date_depart->format('d/m/Y'),
            //'lieu_d' => 509,
            'heure_d' => $reservation->date_depart->format('H:i'),
            'date_f' => $reservation->date_retour->format('d/m/Y'),
            //'lieu_f' => 509,
            'heure_f' => $reservation->date_retour->format('H:i'),
            'id_produit' => 45, // TODO
            'formule' => 1
        ]);


        $result = $this->crawler->post(config('rentiles.admin_path').'/commande_creer.php', [
            'action' => 'ajouter',
            'type_paiement' => 1,
            'statut' => 2, // Payé
            'delaidevis' => 4,
            'type_livraison' => 2,
            'fraisport' => null,
            'forfait_perso' => $reservation->montant,
            'acompte' => null,
            'statut_acompte' => 2, // TODO
            'livraison_infosup' => $reservation->infosup,
            'livraison_adresse' => $reservation->lieu_depart,
            'retour_adresse' => $reservation->lieu_retour,
            'client' => $reservation->nom,
            'id_client' => '260313210511DOE', // TODO
            //'id_adrlivr' => 173,
            'prenom' => $reservation->prenom,
            'telfixe' => $reservation->telephone,
            'email1' => $reservation->email,
            'mdp' => null,
            'nom' => $reservation->nom,

        ]);

        dd($result);
    }

}
