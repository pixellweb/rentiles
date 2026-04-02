<?php

namespace PixellWeb\Rentiles\app\Mapper;

use Illuminate\Support\Collection;
use Ipsum\Reservation\app\Models\Categorie\Categorie;
use Ipsum\Reservation\app\Models\Lieu\Lieu;
use Ipsum\Reservation\app\Models\Prestation\Prestation;
use Ipsum\Reservation\app\Models\Reservation\Condition;
use Ipsum\Reservation\app\Models\Reservation\Etat;
use Ipsum\Reservation\app\Models\Reservation\Moyen;
use Ipsum\Reservation\app\Models\Reservation\Reservation As IpsumReservation;
use Ipsum\Reservation\app\Models\Reservation\Type;
use PixellWeb\Rentiles\app\Data\CreateReservationData;
use PixellWeb\Rentiles\app\Data\OptionData;
use PixellWeb\Rentiles\app\Data\ReservationData;
use PixellWeb\Rentiles\app\RentilesException;

class ReservationMapper
{
    public Collection $categories_mapping;
    public Collection $lieux_mapping;
    public Collection $prestations_mapping;

    public function __construct()
    {
        $this->categories_mapping = Categorie::select(['id', 'custom_fields'])
            ->get()
            ->mapWithKeys(function ($item, $key) {
                return [$item->custom_fields->rentiles_code => $item->id];
            });

        $this->lieux_mapping = Lieu::select(['id', 'custom_fields'])
            ->get()
            ->mapWithKeys(function ($item, $key) {
                return [ $item->custom_fields->rentiles_code => $item->id];
            });

        $this->prestations_mapping = Prestation::with('tarification')
            ->get()
            ->mapWithKeys(function ($item, $key) {
                return [$item->custom_fields->rentiles_code => $item];
            });
    }

    public function updateOrCreate(ReservationData $reservation_data): IpsumReservation
    {
        $observation = $reservation_data->infosup;

        if ($reservation_data->conducteur_additionnel->count()) {
            $observation .= "\nConducteur additionnel : ".$reservation_data->conducteur_additionnel->implode("\n");
        }

        if ($reservation_data->adresse_sur_place) {
            $observation .= "\nAdresse de résidence sur place : ".$reservation_data->adresse_sur_place;
        }

        if (!$this->hasCategorieIpsum($reservation_data->categorie->reference)) {
            throw new RentilesException('Erreur de mapping de la catégorie : '.$reservation_data->categorie->reference);
        }
        if (!$this->hasLieuIpsum($reservation_data->lieu_depart->nom)) {
            $observation .= "\nLieu de départ : ". $reservation_data->lieu_depart->nom;
        }
        if (!$this->hasLieuIpsum($reservation_data->lieu_retour->nom)) {
            $observation .= "\nLieu de retour : ". $reservation_data->lieu_retour->nom;
        }

        $prestations = [];
        foreach ($reservation_data->options as $option) {
            /* @var OptionData $option */
            if (!$this->hasPrestationIpsum($option->reference)) {
                $observation .= "\nOption : ". $option->quantite .' x '.$option->reference. ' ('.$option->total.'€)';
            } else {
                $prestation = $this->getPrestationIpsum($option->reference);
                $prestations[] = [
                    'id' => $prestation->id,
                    'quantite' => $option->quantite,
                    'nom' => $prestation->nom,
                    'tarif' => $option->total,
                    'tarification' => $prestation->tarification,
                    'tarif_libelle' => prix($option->total).' €',
                    'choix' => null,
                ];
            }
        }


        $data = [
            'reference' => $reservation_data->reference,
            'etat_id' => Etat::VALIDEE_ID,
            'condition_paiement_id' => Condition::LIGNE_ID,
            'source_id' => config('rentiles.source_id'),
            'nom' => $reservation_data->nom,
            'prenom' => $reservation_data->prenom,
            'email' => $reservation_data->email,
            'telephone' => $reservation_data->telephone,
            'adresse' => $reservation_data->adresse,
            'cp' => $reservation_data->code_postal,
            'ville' => $reservation_data->ville,
            'pays_id' => $this->getPaysIpsum($reservation_data->pays),
            'naissance_at' => $reservation_data->date_naissance,
            'naissance_lieu' => $reservation_data->lieu_naissance,
            'permis_numero' => $reservation_data->permis_numero,
            'permis_at' => $reservation_data->permis_date,
            'permis_delivre' => $reservation_data->permis_lieu,
            'observation' => $observation,
            'categorie_id' => $this->getCategorieIpsum($reservation_data->categorie->reference),
            'caution' => $reservation_data->caution,
            'franchise' => $reservation_data->franchise,
            'debut_at' => $reservation_data->date_depart,
            'fin_at' => $reservation_data->date_retour,
            'debut_lieu_id' => $this->getLieuIpsum($reservation_data->lieu_depart->nom),
            'fin_lieu_id' => $this->getLieuIpsum($reservation_data->lieu_retour->nom),
            'prestations' => $prestations,
            'total' => $reservation_data->montant,
            'saved' => 1,
            'created_at' => $reservation_data->date,
        ];

        $reservation_ipsum = IpsumReservation::updateOrCreate(['reference' => $reservation_data->reference], $data);

        // Pas de update des paiements. On ne récupère que le paiement de l'acompte
        if (!$reservation_ipsum->paiements->count()) {
            $reservation_ipsum->paiements()->create([
                'paiement_moyen_id' => Moyen::CB_ID,
                'paiement_type_id' => $reservation_data->montant_paye == $reservation_data->montant ? Type::PAIEMENT_ID : Type::ACOMPTE_ID,
                'montant' => $reservation_data->montant_paye,
                'note' => 'Rentîles',
                'created_at' => $reservation_data->date,
            ]);
        }


        return $reservation_ipsum;
    }

    public function get(IpsumReservation $ipsum_reservation): CreateReservationData
    {
        if (!$this->hasCategorieRentiles($ipsum_reservation->categorie_id)) {
            throw new RentilesException('Erreur de mapping de la catégorie : '.$ipsum_reservation->categorie_id);
        }


        $data = [
            'categorie' => $ipsum_reservation->categorie_id,
            'date_depart' => $ipsum_reservation->debut_at,
            'date_retour' => $ipsum_reservation->fin_at,
            'infosup' => $ipsum_reservation->observation,
            'nom' => $ipsum_reservation->nom,
            'prenom' => $ipsum_reservation->prenom,
            'telephone' => $ipsum_reservation->telephone,
            'email' => $ipsum_reservation->email,
            'lieu_depart' => 11, //$ipsum_reservation->lieuDebut, //'ll',
            'lieu_retour' => 11, //$ipsum_reservation->lieuFin, //'ll',
            'montant' => $ipsum_reservation->total,
            'date' =>  $ipsum_reservation->created_at,
        ];
        return CreateReservationData::validateAndCreate($data);
    }


    public function hasCategorieIpsum(string $reference): bool
    {
        return isset($this->categories_mapping[$reference]);
    }

    public function getCategorieIpsum(string $reference): ?int
    {
        return $this->categories_mapping[$reference] ?? null;
    }

    public function hasCategorieRentiles(int $id): bool
    {
        return $this->categories_mapping->contains($id);
    }

    public function getCategorieRentiles(int $id): ?string
    {
        return array_search($id, $this->categories_mapping->toArray()) ?? null;
    }

    public function hasLieuIpsum(string $nom): bool
    {
        return isset($this->lieux_mapping[$nom]);
    }

    public function getLieuIpsum(string $nom): ?int
    {
        return $this->lieux_mapping[$nom] ?? $this->lieux_mapping->first();
    }

    public function hasPrestationIpsum(string $nom): bool
    {
        return isset($this->prestations_mapping[$nom]);
    }

    public function getPrestationIpsum(string $reference): ?Prestation
    {
        return $this->prestations_mapping[$reference] ?? null;
    }

    public function getPaysIpsum(string $nom): ?int
    {
        $maping =  [
            // Pays avec une correspondance de texte
            'Afghanistan' => 1,
            'Afrique du Sud' => 201,
            'Albanie' => 2,
            'Algérie' => 4,
            'Allemagne' => 84,
            'Andorre' => 6,
            'Angola' => 7,
            'Antigua-et-Barbuda' => 8,
            'Arabie saoudite' => 192,
            'Argentine' => 10,
            'Arménie' => 16,
            'Australie' => 11,
            'Autriche' => 12,
            'Azerbaïdjan' => 9,
            'Bahamas' => 13,
            'Bahreïn' => 14,
            'Bangladesh' => 15,
            'Barbade' => 17,
            'Belgique' => 18,
            'Belize' => 26,
            'Bénin' => 59,
            'Bhoutan' => 20,
            'Bolivie' => 21,
            'Bosnie-Herzégovine' => 22,
            'Botswana' => 23,
            'Brésil' => 25,
            'Bulgarie' => 31,
            'Burundi' => 33,
            'Cambodge' => 35,
            'Cameroun' => 36,
            'Cap-Vert' => 38,
            'Chili' => 43,
            'Chine' => 44,
            'Chypre' => 57,
            'Colombie' => 48,
            'Comores' => 49,
            'Costa Rica' => 54,
            'Côte d\'Ivoire' => 110,
            'Croatie' => 55,
            'Cuba' => 56,
            'Danemark' => 60,
            'Djibouti' => 79,
            'Dominique' => 61,
            'Espagne' => 203,
            'Estonie' => 68,
            'Fidji' => 72,
            'Finlande' => 73,
            'France métropolitaine' => 75,
            'Gabon' => 80,
            'Gambie' => 82,
            'Géorgie' => 81,
            'Ghana' => 85,
            'Grèce' => 88,
            'Grenade' => 90,
            'Guatemala' => 93,
            'Guinée' => 94,
            'Guinée équatoriale' => 65,
            'Guyana' => 95,
            'Haïti' => 96,
            'Honduras' => 99,
            'Hongrie' => 101,
            'Inde' => 103,
            'Indonésie' => 104,
            'Iraq' => 106,
            'Irlande' => 107,
            'Islande' => 102,
            'Israël' => 108,
            'Italie' => 109,
            'Jamaïque' => 111,
            'Japon' => 112,
            'Jordanie' => 114,
            'Kazakhstan' => 113,
            'Kenya' => 115,
            'Kirghizistan' => 119,
            'Kiribati' => 87,
            'Koweït' => 118,
            'Lesotho' => 122,
            'Lettonie' => 123,
            'Liban' => 121,
            'Liechtenstein' => 126,
            'Lituanie' => 127,
            'Luxembourg' => 128,
            'Madagascar' => 130,
            'Malaisie' => 132,
            'Malawi' => 131,
            'Maldives' => 133,
            'Mali' => 134,
            'Malte' => 135,
            'Maroc' => 144,
            'Maurice' => 138,
            'Mauritanie' => 137,
            'Mexique' => 139,
            'Monaco' => 140,
            'Mongolie' => 141,
            'Mozambique' => 145,
            'Namibie' => 147,
            'Nauru' => 148,
            'Népal' => 149,
            'Nicaragua' => 156,
            'Niger' => 157,
            'Norvège' => 161,
            'Nouvelle-Zélande' => 155,
            'Oman' => 146,
            'Ouganda' => 224,
            'Ouzbékistan' => 235,
            'Pakistan' => 167,
            'Panama' => 168,
            'Paraguay' => 170,
            'Pays-Bas' => 150,
            'Pérou' => 171,
            'Philippines' => 172,
            'Pologne' => 174,
            'Portugal' => 175,
            'Qatar' => 179,
            'République centrafricaine' => 40,
            'République dominicaine' => 62,
            'République tchèque' => 58,
            'Roumanie' => 181,
            'Royaume-Uni' => 228,
            'Rwanda' => 183,
            'Sainte-Lucie' => 187,
            'Saint-Marin' => 190,
            'Saint-Vincent-et-les Grenadines' => 189,
            'Sao Tomé-et-Principe' => 191,
            'Sénégal' => 193,
            'Seychelles' => 194,
            'Sierra Leone' => 195,
            'Singapour' => 196,
            'Slovaquie' => 197,
            'Slovénie' => 199,
            'Somalie' => 200,
            'Soudan' => 205,
            'Sri Lanka' => 41,
            'Suède' => 209,
            'Suisse' => 210,
            'Suriname' => 206,
            'Swaziland' => 208,
            'Tadjikistan' => 212,
            'Tchad' => 42,
            'Thaïlande' => 213,
            'Togo' => 214,
            'Tonga' => 216,
            'Trinité-et-Tobago' => 217,
            'Tunisie' => 219,
            'Turkménistan' => 221,
            'Turquie' => 220,
            'Tuvalu' => 223,
            'Ukraine' => 225,
            'Uruguay' => 234,
            'Vanuatu' => 154,
            'Venezuela' => 236,
            'Yémen' => 239,
            'Zambie' => 241,
            'Zimbabwe' => 202,
            'Guadeloupe' => 91,
            'Guyane Française' => 76,
            'Martinique' => 136,
            'Mayotte' => 50,
            'Nouvelle-Calédonie' => 153,
            'Polynésie française' => 77,

            // Correspondance manuelle
            /*'Belau' => todo,
            'Biélorussie' => todo,
            'Birmanie' => todo,
            'Brunei' => todo,
            'Burkina' => todo,*/
            'Congo' => 51,
            /*'Cook' => todo,
            'Corée du Nord' => todo,
            'Corée du Sud' => todo,
            'Égypte' => todo,
            'Émirats arabes unis' => todo,
            'Équateur' => todo,
            'Érythrée' => todo,
            'Éthiopie' => todo,
            'Guinée-Bissao' => todo,*/
            'Iran' => 105,
            /*'Laos' => todo,
            'Liberia' => todo,
            'Libye' => todo,
            'Macédoine' => todo,
            'Marshall' => todo,
            'Micronésie' => todo,
            'Moldavie' => todo,
            'Nigeria' => todo,
            'Niue' => todo,
            'Papouasie' => todo,*/
            'Russie' => 105,
            /*'Saint-Christophe-et-Niévès' => todo,
            'Salomon' => todo,
            'Salvador' => todo,
            'Samoa occidentales' => todo,
            'Syrie' => todo,
            'Tanzanie' => todo,
            'Vatican' => todo,
            'Viêt Nam' => todo,
            'Yougoslavie' => todo,
            'Zaïre' => todo,*/
            'USA - Alaska' => 231,
            'USA - Arizona' => 231,
            'USA - Arkansas' => 231,
            'USA - California' => 231,
            'USA - Colorado' => 231,
            'USA - Connecticut' => 231,
            'USA - Delaware' => 231,
            'USA - District Of Columbia' => 231,
            'USA - Florida' => 231,
            'USA - Georgia' => 231,
            'USA - Hawaii' => 231,
            'USA - Idaho' => 231,
            'USA - Illinois' => 231,
            'USA - Indiana' => 231,
            'USA - Iowa' => 231,
            'USA - Kansas' => 231,
            'USA - Kentucky' => 231,
            'USA - Louisiana' => 231,
            'USA - Maine' => 231,
            'USA - Maryland' => 231,
            'USA - Massachusetts' => 231,
            'USA - Michigan' => 231,
            'USA - Minnesota' => 231,
            'USA - Mississippi' => 231,
            'USA - Missouri' => 231,
            'USA - Montana' => 231,
            'USA - Nebraska' => 231,
            'USA - Nevada' => 231,
            'USA - New Hampshire' => 231,
            'USA - New Jersey' => 231,
            'USA - New Mexico' => 231,
            'USA - New York' => 231,
            'USA - North Carolina' => 231,
            'USA - North Dakota' => 231,
            'USA - Ohio' => 231,
            'USA - Oklahoma' => 231,
            'USA - Oregon' => 231,
            'USA - Pennsylvania' => 231,
            'USA - Rhode Island' => 231,
            'USA - South Carolina' => 231,
            'USA - South Dakota' => 231,
            'USA - Tennessee' => 231,
            'USA - Texas' => 231,
            'USA - Utah' => 231,
            'USA - Vermont' => 231,
            'USA - Virginia' => 231,
            'USA - Washington' => 231,
            'USA - West Virginia' => 231,
            'USA - Wisconsin' => 231,
            'USA - Wyoming' => 231,
            //'Colombie-Britannique' => todo,
            'Canada - Alberta' => 37,
            'Canada - Saskatchewan' => 37,
            'Canada - Manitoba' => 37,
            'Canada - Ontario' => 37,
            'Canada - Québec' => 37,
            'Canada - Nouveau-Brunswick' => 37,
            'Canada - Nouvelle-Écosse' => 37,
            'Canada - Île-du-Prince-Édouard' => 37,
            'Canada - Terre-Neuve-et-Labrador' => 37,
            'Canada - Yukon' => 37,
            'Canada - Territoires-du-Nord-Ouest' => 37,
            'Canada - Nunavut' => 37,
            'Réunion(La)' => 180,
            'St Pierre et Miquelon' => 37,
            'Wallis-et-Futuna' => 237,
            'USA - Alabama' => 231,
        ];

        return $maping[$nom] ?? null;
    }
}


