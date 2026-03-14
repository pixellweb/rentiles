<?php

namespace PixellWeb\Rentiles\app\Data;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PixellWeb\Rentiles\app\Enum\Statut;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Ipsum\Reservation\app\Models\Reservation as IpsumReservation;


class ReservationData extends Data
{
    public function __construct(
        public string $reference,
        public string $categorie,
        public Statut $statut,
        public ?array $options,
        public int $montant,

        public ?int $acompte,

        #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/y H:i:s')]
        public Carbon $date,

        public string $prenom,
        public string $nom,
        public ?string $adresse,
        public ?string $code_postal,
        public ?string $ville,
        public ?string $pays,
        public ?string $telephone,
        public ?string $email,

        public ?string $permis_numero,
        public ?string $permis_lieu,
        public ?string $permis_date,
        public ?string $date_naissance,
        public ?string $lieu_naissance,
        public ?string $franchise,
        public ?string $caution,

        public string $lieu_depart,
        public string $lieu_retour,

        #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/Y H:i')]
        public Carbon $date_depart,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/Y H:i')]
        public Carbon $date_retour,
        public ?string $infosup,

    ) {
    }
}

/*
 *
ajax 	1
action 	formresasubmit
admin_tpl 	commandecreer
id_categorie 	1
typetarif 	internet
date_d 	19/03/2026
lieu_d 	509
lieu_d_value 	#LIEU_D
heure_d 	10:00
date_f 	21/03/2026
lieu_f 	509
lieu_f_value 	#LIEU_F
heure_f 	01:00
id_produit 	45
formule 	1

type_paiement=34
statut=2
delaidevis=4
type_livraison=2
fraisport=
forfait_perso=
acompte=11
statut_acompte=2
livraison_infosup=
livraison_adresse=
retour_adresse=
client=john
id_client=260313210511DOE
id_adrlivr=173
prenom=doe
telfixe=02335555555
email1=test40example.com&mdp=

cmd_id=1517
cmd_ref=A001517
srr=1
editcmd_vehicule[ref]=cata
editcmd_vehicule[titre]=Catégorie 1
editcmd_vehicule[type]=produit&
ditcmd_vehicule[prixu]=55
editcmd_vehicule[prixuqte]=55
editcmd_options[1][ref]=bebe
editcmd_options[1][titre]=Siège bébé (de 0 à 3 ans)
editcmd_options[1][type]=option
editcmd_options[1][prixu]=0
editcmd_options[1][qte]=1
editcmd_options[1][prixuqte]=0
editcmd_newoptions[0][ref]=
editcmd_newoptions[0][titre]=
editcmd_newoptions[0][prixu]=
editcmd_newoptions[0][qte]=
editcmd_newoptions[0][prixuqte]=
editcmd_acompteid=3651
editcmd_acompte=11
editcmd_prenom=doe
editcmd_nom=john
editcmd_adresse1=
editcmd_adresse2=
editcmd_cpostal=
editcmd_ville=
editcmd_pays=64
editcmd_tel=02335555555
editcmd_email=test@example.com
editcmd_adresse_residence=
editcmd_nbpersonnes=
editcmd_permis=
editcmd_lieu_permis=
editcmd_date_permis1=0
editcmd_date_permis2=0
editcmd_date_permis3=0
editcmd_date_naissance1=0
editcmd_date_naissance2=0
editcmd_date_naissance3=0
editcmd_lieu_naissance=
editcmd_modele=
editcmd_categorie=
editcmd_immat=
editcmd_franchise=1500
editcmd_caution=1500
editcmd_commentaires=
editcmd_lieud=Aéroport de Martinique Aimé Césaire
editcmd_dated=18/03/2026
editcmd_heured=10:00
editcmd_lieuf=Aéroport de Martinique Aimé Césaire
editcmd_datef=20/03/2026
editcmd_heuref=10:00
editcmd_nbjours=2
editcmd_adresse=Aéroport de Martinique Aimé Césaire
editcmd_infosup=
editcmd_adresse_retour=Aéroport de Martinique Aimé Césaire
editcmd_infosup_retour=
id_produit=45
produit_img=http://s318850998.onlinehome.fr/srr-promolease/client/cache/produit/329_225_0_0_0_1_FFF_picanto_101.jpg&url_speedyrent=http://s318850998.onlinehome.fr/srr-promolease

 public function __construct(
    public string $reference,
    public string $categorie,
    public Statut $statut,
    public ?array $options,
    public int $montant,

    #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/y H:i:s')]
    #[MapInputName('date')]
    public Carbon $created_at,

    public string $prenom,
    public string $nom,
    public ?string $adresse,
    public ?string $code_postal,
    public ?string $ville,
    public ?string $pays,
    public ?string $telephone,
    public ?string $email,

    public ?string $permis_numero,
    public ?string $permis_lieu,
    public ?string $permis_date,
    public ?string $date_naissance,
    public ?string $lieu_naissance,
    public ?string $franchise,
    public ?string $caution,

    public string $lieu_depart,
    public string $lieu_retour,

    #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/Y H:i')]
    public Carbon $date_depart,
    public ?string $debut_at,
    #[WithCast(DateTimeInterfaceCast::class, format: 'd/m/Y H:i')]
    public Carbon $date_retour,
    public ?string $fin_at,
    public ?string $infosup,

) {
    $this->debut_at = $date_depart->format('Y-m-d\TH:i');
    $this->fin_at = $date_retour->format('Y-m-d\TH:i');
}
 */