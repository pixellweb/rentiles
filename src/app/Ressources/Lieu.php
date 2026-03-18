<?php

namespace PixellWeb\Rentiles\app\Ressources;

use Carbon\Carbon;
use PixellWeb\Rentiles\app\Data\CategorieData;
use Illuminate\Support\Facades\Cache;
use PixellWeb\Rentiles\app\Data\LieuData;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Lieu extends Ressource
{
    public function all(): \Spatie\LaravelData\DataCollection
    {
        $result = Cache::remember('commande_creer', 60*60, function () {
            return $this->crawler->get(config('rentiles.admin_path').'/commande_creer.php');
        });

        $dom_crawler = new DomCrawler($result);

        $categories = $dom_crawler->filter('#lieu_d option')->each(function (DomCrawler $element) {
            $data = [];
            $data['id'] = $element->first()->attr('value');
            $data['nom'] = $element->first()->text();

            return $data;
        });

        return LieuData::collection($categories);
    }
}