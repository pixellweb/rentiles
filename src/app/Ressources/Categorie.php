<?php

namespace PixellWeb\Rentiles\app\Ressources;

use PixellWeb\Rentiles\app\Data\CategorieData;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Categorie extends Ressource
{
    public function all(): \Spatie\LaravelData\DataCollection
    {
        $result = $this->crawler->get(config('rentiles.admin_path').'/parcourir.php', [
            'parent' => 1
        ]);

        $dom_crawler = new DomCrawler($result);

        $categories = $dom_crawler->filter('#resulproduit > ul:not([style])')->each(function (DomCrawler $element) {
            $data = [];
            $data['id'] = str_replace('id : ', '', $element->filter(".vignette img")->first()->attr('title'));
            $data['reference'] = $element->filter(".texte_noedit")->first()->text();
            $data['nom'] = $element->filter(".texte_edit")->first()->text();

            return $data;
        });

        return CategorieData::collection($categories);
    }
}