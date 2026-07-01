<?php

namespace PixellWeb\Rentiles\app\Ressources;

use GuzzleHttp\Exception\GuzzleException;
use PixellWeb\Rentiles\app\RentilesException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Client extends Ressource
{
    /**
     * @throws RentilesException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function create(string $nom, string $email, string $prenom = null): string
    {

        $result = $this->crawler->get(config('rentiles.admin_path').'/ajoutcli.php', [
            'action' => 'ajouter',
            'ref' => null,
            'nom' => $nom,
            'prenom' => $prenom ?? '-',
            'email1' => $email,
            'telfixe' => null,
            'mdp' => null,
        ]);

        $result_json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        $dom_crawler = new DomCrawler($result_json['html']);

        $dom_client_id = $dom_crawler->filter('#id_client')->first();

        if (!$dom_client_id->count() or $dom_client_id->attr('value') == '') {
            throw new RentilesException("Client non créé");
        }

        return $dom_client_id->attr('value');
    }
}