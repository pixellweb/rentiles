<?php


namespace PixellWeb\Rentiles\app;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\SimpleCache\InvalidArgumentException;


class Request
{
    protected ?string $base_uri = null;


    protected CookieJar $cookies_jar;


    /**
     * Api constructor.
     */
    public function __construct()
    {
        $this->base_uri = 'https://'.config('rentiles.domain').'/'.config('rentiles.path').'/';

        if (cache()->get('pixellweb-rentiles')) {
            $this->cookies_jar = cache()->get('pixellweb-rentiles');
        } else {
            $this->login();
        }
    }


    /**
     * @return mixed
     * @throws GuzzleException
     * @throws RentilesException
     */
    public function login(): mixed
    {
        $this->cookies_jar = new CookieJar();

        $client = new Client([
                'base_uri' => $this->base_uri
            ]
        );

        $options = [
            'form_params' => [
                "identifiant" => config('rentiles.identifiant'),
                "motdepasse" => config('rentiles.password'),
                "action" => 'identifier',
            ],
            'cookies' => $this->cookies_jar,
        ];

        try {
            $response = $client->post('accueil.php', $options);

            $content = $response->getBody()->getContents();

            if ($response->getStatusCode() != 200 or str_contains($content, 'formConnex')) {
                throw new RentilesException("Impossible de se connecter (" . $response->getStatusCode() . ")");
            }

            // On force la durée de vie du cookie. Sinon c'est 2 heures
            foreach ($this->cookies_jar as $cookie) {
                $cookie->setExpires(null);
            }
            cache()->put('pixellweb-rentiles',  $this->cookies_jar);

        } catch (RequestException $exception) {
            throw new RentilesException("Request::login : " . $exception->getMessage());
        }

        return $content;
    }

    public function logout(): void
    {
        cache()->forget('pixellweb-rentiles');
        $this->cookies_jar = new CookieJar();
    }


    /**
     * @param string $method
     * @param string $ressource_path
     * @param array $parameters
     * @param null $query
     * @return string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function request(string $method, string $ressource_path, array $parameters = [], $query = null): string
    {
        $client = new Client(['base_uri' => $this->base_uri]);
        $headers = [
            'cookies' => $this->cookies_jar
        ];

        if ($method == 'GET') {
            $headers['query'] = $parameters;
        } else {
            $headers['query'] = $query;
            $headers['form_params'] = $parameters;
        }

        try {

            $response = $client->request($method, $ressource_path, $headers);

            if ($response->getStatusCode() != 200) {
                throw new RentilesException("Request::".$method." : code http error (" . $response->getStatusCode() . ")  " . $ressource_path, $response->getStatusCode());
            }

            $content = $response->getBody()->getContents();

            if (str_contains($content, 'formConnex')) {
                // Problème de connexion
                $this->login();
                return $this->request($method, $ressource_path, $parameters);
            }

            return $content;

        } catch (RequestException $exception) {
            throw new RentilesException("Request::".$method." : " . $exception->getMessage() . " " . $exception->getResponse()->getBody()->getContents() . ' '.print_r($parameters,true), $exception->getCode(), $exception);
        }
    }


    /**
     * @param string $ressource_path
     * @param array $params
     * @return string
     * @throws GuzzleException
     * @throws RentilesException|InvalidArgumentException
     */
    public function get(string $ressource_path, array $params = []): string
    {
        return $this->request('GET', $ressource_path, $params);
    }

    /**
     * @param string $ressource_path
     * @param array $params
     * @param null $query
     * @return array|int|null
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function post(string $ressource_path, array $params = [], $query = null): array|int|null
    {
        return $this->request('POST', $ressource_path, $params, $query);
    }


    /**
     * @param string $ressource_path
     * @param array $params
     * @param null $query
     * @return string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function put(string $ressource_path, array $params = [], $query = null): string
    {
        return $this->request('PUT', $ressource_path, $params, $query);
    }


    /**
     * @param string $ressource_path
     * @param array $params
     * @param null $query
     * @return string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RentilesException
     */
    public function patch(string $ressource_path, array $params = [], $query = null): string
    {
        return $this->request('PATCH', $ressource_path, $params, $query);
    }

}
