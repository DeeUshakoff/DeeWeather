<?php

namespace App\Controller;

use App\Helpers\GeoLocation;
use App\Helpers\YandexGeocoderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
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

    #[Route('/GetGeoLocationFromSession', methods: ['GET'])]
    public function GetGeoLocationFromSession(Request $request)
    {
        $session = $request->getSession();
        $geoLocation = $session->get('GeoLocation');

        return new JsonResponse($geoLocation);
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
}