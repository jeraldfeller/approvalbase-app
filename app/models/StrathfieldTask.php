<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/26/2019
 * Time: 10:29 AM
 */

namespace Aiden\Models;


class StrathfieldTask extends _BaseModel
{


    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';

        $url = "http://daenquiry.strathfield.nsw.gov.au/Pages/XC.Track/SearchApplication.aspx"
            . "?d=thismonth"
            . "&k=LodgementDate"
            . "&t=";
     
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

    public function getData($html, $da){
        // get estimated cost
        $this->extractEstimatedCost($html, $da);
        return true;
    }

    /**
     * This will set a cookie so we can scrape the DAs
     */
    public function acceptTerms($formData) {

        $url = "http://daenquiry.strathfield.nsw.gov.au/Common/Common/terms.aspx";

        // Add extra values
        $formData["ctl00_rcss_TSSM"] = null;
        $formData["ctl00_script_TSM"] = null;
        $formData["__EVENTTARGET"] = null;
        $formData["__EVENTARGUMENT"] = null;
        $formData['ctl00$ctMain$chkAgree$chk1'] = "on";
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
        }

    }


    protected function extractDocuments($html, $da, $params = null): bool {

        $addedDocuments = 0;
        $url = "http://daenquiry.strathfield.nsw.gov.au/pages/xc.track/Services/ECMConnectService.aspx/GetDocuments";

        $jsonPayload = json_encode([
            "cId" => $da->getCouncilReferenceAlt(),
            "PageIndex" => 0,
            "PageSize" => 100
        ]);

        $requestHeaders = [
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($jsonPayload),
            "Referer: " . $da->getCouncilUrl(),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
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

            return false;
        }

        $data = json_decode($output);
        if (json_last_error() !== JSON_ERROR_NONE) {

            return false;
        }

        if (strlen($data->d) === 0) {
            return false;
        }

        $docsHtml = str_get_html($data->d);
        if ($docsHtml === false) {
            return false;
        }

        $tableRowElements = $docsHtml->find("tr");
        foreach ($tableRowElements as $tableRowElement) {

            if (count($tableRowElement->children()) !== 4) {
                continue;
            }

            $rowHtml = str_get_html($tableRowElement->innertext());
            if ($rowHtml === false) {
                continue;
            }

            $documentDateElement = $rowHtml->find("td", 0);
            if ($documentDateElement === 0) {
                continue;
            }

            $documentDateString = $this->cleanString($documentDateElement->innertext());
            $documentDate = \DateTime::createFromFormat("d/m/Y", $documentDateString);

            $documentNameParentElement = $rowHtml->find("td", 2);
            if ($documentNameParentElement === null) {
                continue;
            }

            $anchorElement = $documentNameParentElement->children(0);
            if ($anchorElement === null) {
                continue;
            }

            $documentName = $this->cleanString($anchorElement->innertext());

            $documentUrl = $this->cleanString($anchorElement->href);
            $documentUrl = str_replace("../../", "/", $documentUrl);
            $documentUrl = "http://daenquiry.strathfield.nsw.gov.au" . $documentUrl;

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                $addedDocuments++;
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