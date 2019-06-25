<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/25/2019
 * Time: 1:56 PM
 */

namespace Aiden\Models;


class PenrithTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        echo $da->getCouncilUrl() . '<br>';
        $url = "http://bizsearch.penrithcity.nsw.gov.au/ePlanning/Pages/XC.Track/SearchApplication.aspx"
            . "?d=thismonth"
            . "&k=LodgementDate"
            . "&t=DA%2CDevApp";


        // Accept terms, from https://github.com/planningalerts-scrapers/penrith/blob/master/scraper.rb:
        // "For some incomprehensible reason, there's an "I agree" page for an RSS feed."
        $this->acceptTerms($this->getAspFormDataByUrl($url));

        $html = $this->scrapeTo($da->getCouncilUrl());
        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'data':
                    $this->extractData($html, $da);
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

        $url = "http://bizsearch.penrithcity.nsw.gov.au/eplanning/Common/Common/Terms.aspx";

        // Add extra values
        $formData["__EVENTTARGET"] = null;
        $formData["__EVENTARGUMENT"] = null;
        $formData['tab_ClientState'] = null;
        $formData['ctl00$login$txtPwd$txt1'] = null;
        $formData['ctl00$login$txtUser$txt1'] = null;
        $formData['ctl00$ctMain1$chkAgree$chk1'] = "on";
        $formData['ctl00$ctMain1$BtnAgree'] = "I Agree";

        $formData = http_build_query($formData);

        $requestHeaders = [
            "Accept: text/html,application/xhtml+xm…plication/xml;q=0.9,*/*;q=0.8",
            "Accept-Encoding: none",
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: " . strlen($formData),
            "Host: bizsearch.penrithcity.nsw.gov.au",
            "Origin: http://bizsearch.penrithcity.nsw.gov.au",
            "Referer: http://bizsearch.penrithcity.nsw.gov.au/eplanning/Common/Common/Terms.aspx",
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


    public function extractData($html, $da)
    {
        $this->extractEstimatedCost($html, $da);
        return true;
    }

    protected function extractDocuments($html, $da, $params = null): bool {

        $addedDocuments = 0;
        $docsHtml = $html;
        if (!$docsHtml) {
            return false;
        }

        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            $regexPattern = '/Common\/Output\/DataWorksAccess\.aspx\?id=/';
            if (preg_match($regexPattern, $anchorElement->href) === 0) {
                continue;
            }

            $documentUrl = str_replace("../../", "/", $anchorElement->href);
            $documentUrl = "http://bizsearch.penrithcity.nsw.gov.au/ePlanning" . $documentUrl;

            $parentElement = $anchorElement->parent();
            if ($parentElement === null) {
                continue;
            }

            $documentNameElement = $parentElement->next_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString(strip_tags($documentNameElement->innertext()));

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

        $container = $html->find('#ctMain1_info_pnlApplication', 0);
        if($container){
            $detail = $container->find('.detail');

            foreach($detail as $row){
                if($row->find('.detailleft', 0) && $row->find('.detailright', 0)){
                    $detailLeft = trim($row->find('.detailleft', 0)->innertext());
                    $detailRight = trim($row->find('.detailright', 0)->innertext());
                    if(strpos($detailLeft, 'Estimated Cost of Work') !== false){
                        $estimatedCost = $this->cleanString($detailRight);
                        return $this->saveEstimatedCost($da, $estimatedCost);
                    }
                }
            }
        }

      return false;

    }

}