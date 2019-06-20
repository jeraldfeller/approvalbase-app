<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/20/2019
 * Time: 12:06 PM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class FairfieldcityTask extends  _BaseModel
{
    public function init($actions, $da, $method ='get')
    {
        echo $da->getCouncilUrl() . '<br>';
        $url = "https://openaccess.fairfieldcity.nsw.gov.au/OpenAccess/Modules/Applicationmaster/default.aspx?page=wrapper&key=010.2019.00000228.001";
        $this->acceptTerms($this->getAspFormDataByUrl($url));
        $html = $this->scrapeTo($da->getCouncilUrl());
        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
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

        $logMsg = "Attempting to accept terms for required cookie(s)...";
        echo $logMsg . "<br>";

        $url = "https://openaccess.fairfieldcity.nsw.gov.au/OpenAccess/Modules/Applicationmaster/default.aspx"
            . "?page=found"
            . "&1=lastmonth"
            . "&4a=10"
            . "&6=F";

        // Add extra values
        $formData["__EVENTTARGET"] = null;
        $formData["__EVENTARGUMENT"] = null;
        $formData['ctl00$cphContent$ctl01$Button1'] = "Agree";
        $formData['ctl00_TopNavMenu_RadMenu1_ClientState'] = null;
        $formData['ctl00_cphContent_ctl01_RadTabStrip1_ClientState'] = '{"selectedIndexes":["0"],"logEntries":[],"scrollState":{}}';
        $formData = http_build_query($formData);

        $requestHeaders = [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Encoding: none",
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: " . strlen($formData),
            "Host: openaccess.fairfieldcity.nsw.gov.au",
            "Referer: https://openaccess.fairfieldcity.nsw.gov.au/OpenAccess/Modules/Applicationmaster/default.aspx?page=found&1=lastmonth&4a=10&6=F"
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

            $logMsg = sprintf("Terms could not be accepted, cURL error: [%s] (%s)", $errmsg, $errno);
            echo $logMsg . "<br>";
            return false;
        }
        else {

            $logMsg = "Terms were accepted. Cookie(s) was/were set";
            echo $logMsg . "<br>";
            return true;
        }

    }

    protected function extractDocuments($html, $da, $params = null): bool
    {
        echo "Extracting Documents <br>";
        $documentsAdded = 0;
        $documentsElement = $html->find("[id=lblDocs]", 0);

        if ($documentsElement !== null) {

            $regexPattern = '/(([0-9]{1,2}\/[0-9]{2}\/[0-9]{4}) (.+?) - (.+?) \[<a href="(.+?)" target="_blank">View<\/a>]<br>)/';
            if (preg_match_all($regexPattern, $documentsElement->innertext(), $matches) !== 0) {

                $amountOfDocuments = count($matches[0]);
                for ($i = 0; $i < $amountOfDocuments; $i++) {

                    // Generate Document Date
                    $documentDate = \DateTime::createFromFormat("j/m/Y", $matches[2][$i]);

                    // Generate Document Name
                    $documentName = $this->cleanString($matches[4][$i]);

                    // Generate Document URL
                    $documentUrl = $matches[5][$i];
                    $documentUrl = str_replace("../../", "/", $documentUrl);
                    $documentUrl = "https://openaccess.fairfieldcity.nsw.gov.au/OpenAccess" . $documentUrl;

                    if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                        $documentsAdded++;
                    }
                }
            }
        }

        return ($documentsAdded > 0);
    }
}