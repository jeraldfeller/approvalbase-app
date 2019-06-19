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
        echo 'URL : ' . $url . '<br>';
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
                        case 'documents':
                            $this->extractDocuments($htmlData, $da);
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
        $this->extractLodgeDate($html, $da);
        return true;
    }

    public function extractLodgeDate($html, $da)
    {
        echo "extracting lodge date....<br>";
        $tdElements = $html->find("td");
        foreach ($tdElements as $tdElement) {

            $tdText = $this->cleanString($tdElement->innertext());
            if (strpos(strtolower($tdText), "submitted date") === false) {
                continue;
            }

            $valueElement = $tdElement->next_sibling();
            if ($valueElement === null) {
                continue;
            }

            $value = $this->cleanString($valueElement->innertext());
            $date = \DateTime::createFromFormat("d/m/Y", $value);
            return ($this->saveLodgeDate($da, $date));
        }

        return false;
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

    protected function extractDocuments($html, $da, $params = null): bool
    {
        $addedDocuments = 0;

        $tableElement = $html->find("table[id=doc-table]", 0);
        if ($tableElement === null) {
            return false;
        }

        $tbodyElement = $tableElement->children(1);
        if ($tbodyElement === null) {
            return false;
        }

        foreach ($tbodyElement->children() as $tableRowElement) {

            $documentNameElement = $tableRowElement->children(1);
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString($documentNameElement->innertext());

            // URL
            $anchorElement = $tableRowElement->children(4)->children(0);
            $documentUrl = $this->cleanString($anchorElement->href);

            if ($this->saveDocument($da, $documentName, $documentUrl, $da->getLodgeDate())) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);
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