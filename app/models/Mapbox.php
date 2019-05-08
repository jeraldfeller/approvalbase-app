<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 12/17/2018
 * Time: 8:30 AM
 */

namespace Aiden\Models;


class Mapbox extends _BaseModel
{

    public static function geocode($address, $country = 'au'){
        $address = urlencode($address);
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $apiKey = $config->mapboxApiKey;

        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . $address . ".json?country=".$country."&access_token=" . $apiKey;

        $response = json_decode(self::curlTo($url));
        if (count($response->features) > 0) {
            // get most relevant
            $center = $response->features[0]->center;
            $long = $center[0];
            $lat = $center[1];
        } else {
            $long = 0;
            $lat = 0;
        }
        return array(
            'longitude' => $long,
            'latitude' => $lat
        );
    }
    public static function curlTo($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 40d9d6dc-6e61-47f0-9508-34b9fcf8d76b",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        }

        return $response;
    }

}