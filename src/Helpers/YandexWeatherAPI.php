<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Exception;
use Symfony\Component\HttpClient\HttpClient;

class YandexWeatherAPI
{
    private string $base_uri = "https://api.weather.yandex.ru/v2/forecast?";
    private string $api_key;
    private Client $client;
    private array $query;

    public Response $Response;

    public function __construct(string $api_key)
    {
        $this->api_key = $api_key;
        $this->client = new Client();
    }

    public function SetGeoLocation(GeoLocation $location): void
    {
        $this->query['lat'] = $location->Latitude;
        $this->query['lon'] = $location->Longitude;
    }

    public function SetLanguage(string $language): void
    {
        $this->query['lang'] = $language;
    }

    public function ExecuteForecast(): bool
    {
        $headers['X-Yandex-API-Key'] = $this->api_key;
        $options = ['query' => $this->query, 'headers' => $headers];

        $request = new Request('GET', $this->base_uri);

        $this->Response = $this->client->send($request, $options);

//        try{
//        }S
//        catch (GuzzleException $e) {
//            return false;
//        }
//        if($this->Response->getStatusCode() != 200){
//            return false;
//        }
//       $s_client = HttpClient::createForBaseUri($this->base_uri);
//       $s_response = $s_client->request('GET', $this->base_uri, $options);
        return true;
    }

    public const CONDITIONS = [
        'clear' => 'Ясно',
        'partly-cloudy' => 'Малооблачно',
        'cloudy' => 'Облачно с прояснениями',
        'overcast' => 'Пасмурно',
        'light-rain' => 'Небольшой дождь',
        'rain' => 'Дождь',
        'heavy-rain' => 'Сильный дождь',
        'showers' => 'Ливень',
        'wet-snow' => 'Дождь со снегом',
        'light-snow' => 'Небольшой снег',
        'snow' => 'Снег',
        'snow-showers' => 'Снегопад',
        'hail' => 'Град',
        'thunderstorm' => 'Гроза',
        'thunderstorm-with-rain' => 'Дождь с грозой',
        'thunderstorm-with-hail' => 'Гроза с градом'
    ];
    public const WIND_DIRECTIONS = [
        'nw' => 'Северо-западное',
        'n' => 'Северное',
        'ne' => 'Северо-восточное',
        'e' => 'Восточное',
        'se' => 'Юго-восточное',
        's' => 'Южное',
        'sw' => 'Юго-западное',
        'w' => 'Западное',
        'c' => 'Штиль'
    ];
    public const DAY_PARTS = [
        'night' => "Ночью",
        'morning' => 'Утром',
        'day' => 'Днем',
        'evening' => 'Вечером'
    ];
}