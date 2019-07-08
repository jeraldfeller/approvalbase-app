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

    public function getData($html, $da){
        $this->extractOfficers($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractLodgeDate($html, $da);
        $this->extractPeople($html, $da);
        $this->extractDescription($html, $da);
        $this->extractEstimatedCost($html, $da);
    }


    protected function extractAddresses($html, $da, $params = null): bool {

        $addedAddresses = 0;
        $addressesElement = $html->find("div[id=lblProperties]", 0);

        if ($addressesElement !== null) {

            $addressesArray = explode("<br>", $addressesElement->innertext());
            foreach ($addressesArray as $daAddress) {

                $daAddress = $this->cleanString($daAddress);
                if (strlen($daAddress) === 0) {
                    continue;
                }

                if ($this->saveAddress($da, $daAddress) === true) {
                    $addedAddresses++;
                }
            }
        }

        return ($addedAddresses > 0);

    }

    protected function extractPeople($html, $da, $params = null): bool {

        // The council called this element peeps, just following their naming...
        $peepsCreated = 0;
        $peepsElement = $html->find("div[id=lblPeeps]", 0);

        if ($peepsElement !== null) {

            foreach (explode("<br />", $peepsElement->innertext()) as $peep) {

                $peepArray = explode(":", $peep);
                if (count($peepArray) === 1) {

                    if (strlen($peepArray[0]) === 0) {
                        continue;
                    }

                    $role = "Applicant";
                    $name = trim($this->cleanString($peepArray[0]));
                }
                else {

                    if (strlen($peepArray[1]) === 0) {
                        continue;
                    }

                    $role = trim($this->cleanString($peepArray[0]));
                    $name = trim($this->cleanString($peepArray[1]));
                }

                if ($this->saveParty($da, $role, $name)) {
                    $peepsCreated++;
                }
            }
        }

        return ($peepsCreated > 0);

    }

    protected function extractOfficers($html, $da, $params = null): bool {

        $officersCreated = 0;
        $officerElement = $html->find("div[id=lblOff]", 0);

        if ($officerElement !== null) {

            $role = "Officer";
            $name = $this->cleanString($officerElement->innertext());

            if ($this->saveParty($da, $role, $name) === true) {
                $officersCreated++;
            }
        }

        return ($officersCreated > 0);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $estimatedCostElement = $html->find("[id=lblDim]", 0);

        if ($estimatedCostElement !== null) {
            return $this->saveEstimatedCost($da, $estimatedCostElement->innertext());
        }
        return false;

    }

    protected function extractDescription($html, $da, $params = null): bool {

        $tdElements = $html->find("td");

        foreach ($tdElements as $tdElement) {

            $tdText = $this->cleanString($tdElement->innertext());
            if (strpos(strtolower($tdText), "description") === false) {
                continue;
            }

            $valueElement = $tdElement->next_sibling();
            if ($valueElement === null) {
                continue;
            }

            $value = $this->cleanString($valueElement->innertext());
            return $this->saveDescription($da, $value);
        }

        return false;

    }

    protected function extractLodgeDate($html, $da, $params = null): bool {

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
            return $this->saveLodgeDate($da, $date);
        }

        return false;

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