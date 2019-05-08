<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/23/2019
 * Time: 10:53 AM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;
class CamdenTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {
        $url = $da->getCouncilUrl();
        $html = ($method == 'get' ? $this->curlToGet($url) : $this->curlToGet($url));
        if ($html) {
            $htmlData = str_get_html($html);
            $alertInfo = $htmlData->find('.alert-info', 0);
            $alert = ($alertInfo ? $alertInfo->innerText() : '');
            if(strpos($alert, 'application is not available') === false){
                foreach ($actions as $a) {
                    switch ($a) {
                        case 'data':
                            $this->getData($htmlData, $da);
                            break;
                    }
                }
            }else{
                echo 'DA is no longer available. <br>';
                // da is no longer available
                $dd = new DeletedDa();
                $dd->setCouncilReference($da->getCouncilReference());
                $dd->setCouncilUrl($da->getCouncilUrl());
                $dd->save();
                $da->delete();
            }

        }

    }

    public function getData($html, $da)
    {
        // get estimated cost
        $this->getEstimatedCost($html, $da);
        return true;
    }

    public function getEstimatedCost($html, $da){
        $estimatedCostHeaderElement = $html->find("#estimatedCost", 0);
        if ($estimatedCostHeaderElement === null) {
            return false;
        }
        $divElement = $estimatedCostHeaderElement->next_sibling();
        if($divElement == null){
            return false;
        }
        $estimatedCostValue = self::cleanString($divElement->innertext());
        return $this->saveEstimatedCost($da, $estimatedCostValue);
    }

    public function curlToGet($url){
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
                "Postman-Token: 34b89c43-47fa-44df-9ab3-c9a8cdd0060f",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }
}