<?php

namespace App\Controller;

use App\Helpers\GeoLocation;
use App\Helpers\YandexGeocoderAPI;
use App\Helpers\YandexWeatherAPI;
use GuzzleHttp\Client;
use http\Env;
use phpDocumentor\Reflection\Types\Mixed_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function Home(Request $request): Response
    {
        $session = $request->getSession();
        $geoLocation = $session->get('GeoLocation');

        if (is_null($geoLocation)) {
            $geoLocation = "";
            return new Response($this->render('bundles/TwigBundle/Exception/error.html.twig', ['status_text' => 'Укажите город']));
        }

        $yandexWeatherAPI = new YandexWeatherAPI($this->getParameter('yandex.api.key'));

        $yandexWeatherAPI->SetGeoLocation($geoLocation);
        $yandexWeatherAPI->SetLanguage('ru_RU');

        if (!$yandexWeatherAPI->ExecuteForecast()) {
            return new Response($this->render('error.html.twig', ['error_name' => 'yandex api error']));
        }

        $weatherApiResponse = json_decode($yandexWeatherAPI->Response->getBody());
        $normalizedNames = ['condition' => YandexWeatherAPI::CONDITIONS[$weatherApiResponse->fact->condition]];
        $normalizedNames['windDirection'] = YandexWeatherAPI::WIND_DIRECTIONS[$weatherApiResponse->fact->wind_dir];
        $geoObject = $weatherApiResponse->geo_object;

        $country = $geoObject->country->name;
        $city = $geoObject->locality->name;
        $geoLocation->Name = "$country, $city";


        $normalizedDays = [];
        $forecastDaysLimit = $request->query->getInt('forecastDaysLimit');

        if ($forecastDaysLimit != 0) {
            if($forecastDaysLimit > 7){
                $forecastDaysLimit = 7;
            }

            for ($i = 0; $i < $forecastDaysLimit; $i++) {

                $forecast = $weatherApiResponse->forecasts[$i];
                $day = ['parts' => []];
                $day['date'] = $forecast->date;
                foreach ($forecast->parts as $key => $dayPart) {
                    if ($key == 'day_short' || $key == 'night_short') {
                        continue;
                    }
                    $normalizedDayPart = [];
                    $normalizedDayPart['name'] = YandexWeatherAPI::DAY_PARTS[$key];
                    $normalizedDayPart['wind_speed'] = $dayPart->wind_speed;
                    $normalizedDayPart['wind_dir'] = YandexWeatherAPI::WIND_DIRECTIONS[$dayPart->wind_dir];
                    $normalizedDayPart['condition'] = YandexWeatherAPI::CONDITIONS[$dayPart->condition];
//                $normalizedDayPart['uv_index']=$dayPart->uv_index;
                    $normalizedDayPart['uv_index'] = 15;
                    $normalizedDayPart['temp_avg'] = $dayPart->temp_avg;

                    $day['parts'][$key] = $normalizedDayPart;
                }
                $normalizedDays[] = $day;
            }
        }
        return new Response($this->render('home.twig',
            ['GeoLocationName' => $geoLocation,
                'weather' => $weatherApiResponse,
                'normalizedNames' => $normalizedNames,
                'normalizedDays' => $normalizedDays
            ]));
    }

    #[Route('/GetGeoLocationByName/{name?}', methods: ['GET'])]
    public function GetGeoLocationByName($name)
    {
        $yandexGeocodeAPI = new YandexGeocoderAPI($this->getParameter('yandex.geocode.api.key'));
        $yandexGeocodeAPI->SetGeocode($name);
        $yandexGeocodeAPI->ExecuteForecast();
        $geocoderAPIResponse = json_decode($yandexGeocodeAPI->Response->getBody());
        $foundedLocations = $geocoderAPIResponse->response->GeoObjectCollection->featureMember;
        if (count($foundedLocations) === 0) {
            return new JsonResponse([], 404);
        }
        $normalizedFoundedLocations = [];
        foreach ($foundedLocations as $location) {
            $originalGeoObject = $location->GeoObject;
            $normalizedLocation = [];
            $normalizedLocation['name'] = $originalGeoObject->name;
            $normalizedLocation['description'] = $originalGeoObject->description;

            $coords = explode(' ', $originalGeoObject->Point->pos);

            $normalizedLocation['longitude'] = $coords[0];
            $normalizedLocation['latitude'] = $coords[1];
            $normalizedFoundedLocations[] = $normalizedLocation;
        }
        return new JsonResponse($normalizedFoundedLocations);
    }


    #[Route('/SetGeoLocation', methods: ['POST'])]
    public function SetGeoLocation(Request $request)
    {
        $longitude = $request->request->get('longitude');
        $latitude = $request->request->get('latitude');

        if (is_null($longitude) || is_null($latitude)) {
            return new JsonResponse("incorrect data", 403);
        }
        $session = $request->getSession();

        $geoLocation = new GeoLocation();
        $geoLocation->Latitude = $latitude;
        $geoLocation->Longitude = $longitude;

        $session->set('GeoLocation', $geoLocation);

        return new JsonResponse($geoLocation);
    }

    #[Route('/GetGeoLocationFromSession', methods: ['GET'])]
    public function GetGeoLocationFromSession(Request $request)
    {
        $session = $request->getSession();
        $geoLocation = $session->get('GeoLocation');

        return new JsonResponse($geoLocation);
    }
}