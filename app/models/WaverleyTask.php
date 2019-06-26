<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/26/2019
 * Time: 1:55 PM
 */

namespace Aiden\Models;


class WaverleyTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';

        $url = "http://eservices.waverley.nsw.gov.au/Pages/XC.Track/SearchApplication.aspx"
            . "?d=lastmonth"
            . "&k=LodgementDate"
            . "&t=A0,SP2A,TPO,B1,B1A,FPS";

        $this->acceptTerms($this->getAspFormDataByUrl($url));


        $html = $this->scrapeTo($da->getCouncilUrl());
        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'data':
                    $this->getData($html, $da);
                    break;
                case 'documents':
                    $this->extractDocuments($html, $da);
                    break;
            }
        }
    }


    /**
     * This will set a cookie so we can scrape the DAs
     */
    public function acceptTerms($formData) {
        $url = "https://eservices.waverley.nsw.gov.au/Common/Common/terms.aspx";

        // Add extra values
        $formData["ctl00_rcss_TSSM"] = null;
        $formData['ctl00$ctMain$BtnAgree'] = "I Agree";
        $formData = http_build_query($formData);

        $requestHeaders = [
            "Accept: */*; q=0.01",
            "Accept-Encoding: none",
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: " . strlen($formData)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);

        if ($errno !== 0) {

            $message = "cURL error: " . $errmsg . " (" . $errno . ")";

            return false;
        }


    }

    public function getData($html, $da){
        // get estimated cost
        $this->extractEstimatedCost($html, $da);
        return true;
    }



    protected function extractDocuments($html, $da, $params = null): bool {
        $addedDocuments = 0;
        $docsHtml = $html;
        if (!$docsHtml) {
            return false;
        }

        $docContainer = $docsHtml->find('#edms', 0);
        if($docContainer){
            $tr = $docContainer->find('.file');
            if(count($tr) > 0){
                for($i = 0; $i < count($tr); $i++){
                    $td = $tr[$i]->find('td', 1);
                    $dateContainer = $tr[$i]->find('td', 2);
                    if($td){
                        $a = $td->find('a', 0);
                        $docName =  $this->cleanString($a->innertext());
                        $docUrl = 'https://eservices.waverley.nsw.gov.au'.str_replace('../..', '', $this->cleanString($a->href));
                        $date = $this->cleanString($dateContainer->innertext());
                        $documentDate = \DateTime::createFromFormat("d/m/Y", $date);
                        if ($this->saveDocument($da, $docName, $docUrl, $documentDate) === true) {
                            $addedDocuments++;
                        }
                    }
                }

            }
        }
        return ($addedDocuments > 0);
    }


    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $container = $html->find('.detail', 0);
        if($container){
            $detailRight = $container->find('.detailright', 0);
            if($detailRight){
                $detail = explode('<br />', $detailRight->innertext());
                for($i = 0; $i < count($detail); $i++){
                    if(strpos($detail[$i], 'Estimated Cost of Work') !== false){
                        $info = explode(':', $detail[$i]);
                        if(count($info) > 1){
                            $header = trim($info[0]);
                            $value = trim($info[1]);
                            $estimatedCost = $this->cleanString($value);
                            return $this->saveEstimatedCost($da, $estimatedCost);
                        }
                    }
                }
            }
        }
        return false;

    }

}