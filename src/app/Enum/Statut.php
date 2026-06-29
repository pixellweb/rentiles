<?php

namespace PixellWeb\Rentiles\app\Enum;

enum Statut: string
{
    case Acompte = "Payé partiellement";
    case Paye = "payé";
    case Differe = "Paiement différé";
}