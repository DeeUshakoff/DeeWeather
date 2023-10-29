<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class YandexGeocoderAPI
{
    private string $base_uri = "https://geocode-maps.yandex.ru/1.x/";
    private string $api_key;
    private Client $client;
    private array $query;

    public Response $Response;
    public function __construct(string $api_key)
    {
        $this->api_key=$api_key;
        $this->client = new Client();

        $this->query['apikey']=$this->api_key;

    }

    public function SetGeocode(string $geocode): void
    {
        $this->query['geocode']= $geocode;
    }
    public function ExecuteForecast() : bool{
        $this->query['format']='json';
        $options = ['query'=>$this->query];

        $request = new Request('GET', $this->base_uri);
        $this->Response = $this->client->send($request, $options);
        return true;
    }
}