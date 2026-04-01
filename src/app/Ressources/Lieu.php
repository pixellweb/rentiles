<?php

namespace PixellWeb\Rentiles\app\Ressources;

use PixellWeb\Rentiles\app\Data\LieuData;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Lieu extends Ressource
{
    public function all(): \Spatie\LaravelData\DataCollection
    {
        $result = $this->crawler->get(config('rentiles.admin_path').'/commande_creer.php');

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