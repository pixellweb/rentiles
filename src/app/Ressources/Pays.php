<?php

namespace PixellWeb\Rentiles\app\Ressources;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Pays extends Ressource
{
    public function all()/*: \Spatie\LaravelData\DataCollection*/
    {
        $result =$this->crawler->get(config('rentiles.admin_path').'/commande_details.php', [
            'ref' => 'A008169' // TODO rechercher le premier dans la liste des résas
        ]);

        $dom_crawler = new DomCrawler($result);

        $pays = $dom_crawler->filter('#editcmd_pays option')->each(function (DomCrawler $element) {
            $data = [];
            $data['id'] = $element->first()->attr('value');
            $data['nom'] = $element->first()->text();

            return $data;
        });

        return $pays;

        //return LieuData::collection($categories);
    }
}