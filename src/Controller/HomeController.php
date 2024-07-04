<?php

namespace App\Controller;

use App\Helpers\YandexWeatherAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            return new Response($this->render('bundles/TwigBundle/Exception/error.html.twig', ['error_name' => 'yandex api error']));
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
}