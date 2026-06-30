<?php

namespace PixellWeb\Rentiles\app\Enum;

enum Statut: int
{
    case Devis = 16;
    case NonPaye = 1;
    case Paye = 2;
    case Envoye = 4;
    case Annule = 5;
    case Acompte = 6;
    case PaiementAgence = 10;
    case Rembourse = 14;
    case Differe = 15;
    case Avoir = 17;
    case AttentePaiement = 19;

}