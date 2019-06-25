<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/25/2019
 * Time: 11:26 AM
 */

namespace Aiden\Models;


class NorthSydneyTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
        $url = "https://apptracking.northsydney.nsw.gov.au/Pages/XC.Track/SearchApplication.aspx"
            . "?d=thismonth&k=LodgementDate";
        if ($this->acceptTerms($this->getAspFormDataByUrl($url)) === false) {
           echo "Terms could not be accepted. Stopping execution. <br>";
            return false;
        }

        echo "Terms Accepted... <br>";
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

        $url = "https://apptracking.northsydney.nsw.gov.au/Common/Common/terms.aspx";

        // Add extra values
        $formData["ctl00_rcss_TSSM"] = null;
        $formData["ctl00_script_TSM"] = null;
        $formData["__EVENTTARGET"] = null;
        $formData["__EVENTARGUMENT"] = null;
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
            echo $message . '<br>';
            return false;
        }else{
            return true;
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


        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            $regexPattern = '/Common\/Output\/Document\.aspx\?id=/';
            if (preg_match($regexPattern, $anchorElement->href) === 0) {
                continue;
            }

            $documentUrl = str_replace("../../", "/", $anchorElement->href);
            $documentUrl = " https://apptracking.northsydney.nsw.gov.au" . $documentUrl;

            $parentElement = $anchorElement->parent();
            if ($parentElement === null) {
                continue;
            }

            $documentNameElement = $parentElement->next_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString($documentNameElement->innertext());

            $documentDateElement = $documentNameElement->next_sibling();
            if ($documentDateElement !== null) {

                $documentDateString = $this->cleanString($documentDateElement->innertext());
                $documentDate = \DateTime::createFromFormat("d/m/Y", $documentDateString);
            }

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $infoContainer = $html->find("#b_ctl00_ctMain_info_app", 0);
        if($infoContainer){
            $infobreak = explode('<br />', $infoContainer->innertext());
            for($i = 0; $i < count($infobreak); $i++){
                $subInfoBreak = explode(':', $infobreak[$i]);
                if($subInfoBreak){
                    if(count($subInfoBreak) > 1){
                        $infoHeader = trim($subInfoBreak[0]);
                        $infoValue = trim($subInfoBreak[1]);
                        echo $infoHeader . ' - ' . $infoValue . '<br>';
                        if(strpos($infoHeader, 'Estimated Cost of Work') !== false){
                            $estimatedCost = $this->cleanString($infoValue);
                            return $this->saveEstimatedCost($da, $estimatedCost);
                        }
                    }
                }

            }
        }

        return false;

    }



}