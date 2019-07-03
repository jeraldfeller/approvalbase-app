<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/18/2019
 * Time: 9:56 AM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;
class CampbelltownTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        $url = "https://ebiz.campbelltown.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx" . "?ModuleCode=LAP";

        // Tell server we're looking for development applications, and retrieve required ASP variables
        $formData = $this->enquiryStep1($url);
        if ($formData === false) {
            $logMsg = "Error getting form data <br>";
            echo $logMsg;
            return false;
        }


        // Tell server we're looking for development applications, and retrieve output from initial page
        $output = $this->enquiryStep2($formData);
        if ($output === false) {
            $logMsg = "Error getting output <br>";
            echo $logMsg;
            return false;
        }



        echo 'Cookies registered...<br>';

        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'data':
                    $councilUrl = $da->getCouncilUrl();
                    echo "Extracting data... \n";
                    echo "Council Url: " . $councilUrl . "<br>";
                    $html = $this->scrapeTo($councilUrl);
                    if ($html) {
                        $htmlData = str_get_html($html);
                        $this->extractData($htmlData, $da);
                    }

                    break;
                case 'documents':
                    $this->extractDocuments("", $da);
                    break;
            }
        }
    }


    /**
     * Tells the server we're looking for development applications
     */
    public function enquiryStep1($url, $calledByScrapeMethod = true) {

        $logMsg = "Telling the server we're looking for development applications, requesting cookies...<br>";
        echo $logMsg;

        $formData = $this->getAspFormDataByUrl($url);
        $formData['ctl00$MainBodyContent$mContinueButton'] = "Next";
        $formData['ctl00$mHeight'] = 653;
        $formData['ctl00$mWidth'] = 786;

        // Page gives different output not allowing us to scrape the addresses, change option to 2 when called by scrapeMeta
        if ($calledByScrapeMethod === true) {
            $formData['mDataGrid:Column0:Property'] = 'ctl00$MainBodyContent$mDataList$ctl03$mDataGrid$ctl04$ctl00';
        }
        else {
            $formData['mDataGrid:Column0:Property'] = 'ctl00$MainBodyContent$mDataList$ctl03$mDataGrid$ctl02$ctl00';
        }

        $formData['__LASTFOCUS'] = null;
        $formData = http_build_query($formData);

        $requestHeaders = [
            "Host: ebiz.campbelltown.nsw.gov.au",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-GB,en;q=0.5",
            "Accept-Encoding: none",
            "Referer: https://ebiz.campbelltown.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx?ModuleCode=LAP",
            "Content-Type: application/x-www-form-urlencoded",
            "Connection: keep-alive",
            "DNT: 1",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
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

            $logMsg = "cURL error in enquiryStep1 function: " . $errmsg . " (" . $errno . ")";
            echo $logMsg . '<br>';
            return false;
        }

        $newFormData = $this->getAspFormDataByString($output);
        return $newFormData;

    }

    /**
     * Tells the server the period we want development applications from
     * @param type $formData
     * @return boolean
     */
    public function enquiryStep2($formData) {

        $logMsg = "Telling server the period we're looking for, requesting more cookies...<br>";
        echo $logMsg;

        $url = "https://ebiz.campbelltown.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquirySearch.aspx";

        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mEnquiryListsDropDownList'] = 23;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mSearchButton'] = "Search";
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetNameTextBox'] = null;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetNumberTextBox'] = null;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetTypeDropDown'] = null;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mSuburbTextBox'] = null;
        $formData['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 1;
        $formData['ctl00$mHeight'] = 653;
        $formData['ctl00$mWidth'] = 786;

        $formData = http_build_query($formData);

        $requestHeaders = [
            "Host: ebiz.campbelltown.nsw.gov.au",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-GB,en;q=0.5",
            "Accept-Encoding: none",
            "Referer: https://ebiz.campbelltown.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx?ModuleCode=LAP",
            "Content-Type: application/x-www-form-urlencoded"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
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

            $logMsg = "cURL error in enquiryStep2 function: " . $errmsg . " (" . $errno . ")";
            $this->logger->info($logMsg);
            return false;
        }

        return $output;

    }

    public function extractData($html, $da)
    {
        $this->extractOfficers($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractApplicants($html, $da);
        $this->extractDescription($html, $da);
        $this->extractLodgeDate($html, $da);
        return true;
    }


    protected function extractAddresses($html, $da, $params = null): bool {

        $addedAddresses = 0;

        $legendElements = $html->find("legend");
        foreach ($legendElements as $legendElement) {

            $legendText = $this->cleanString($legendElement->innertext());
            if (strpos(strtolower($legendText), "address") === false) {
                continue;
            }

            $fieldsetElement = $legendElement->parent();
            if ($fieldsetElement === null) {
                continue;
            }

            // This HTML is so so badly formatted, but luckily they use our required classes when we want them to.
            $fieldsetHtml = \Sunra\PhpSimple\HtmlDomParser::str_get_html($fieldsetElement->innertext());
            if ($fieldsetHtml === false) {
                return false;
            }

            $addressElements = $fieldsetHtml->find("div[class=ContentText],div[class=AlternateContentText]");
            foreach ($addressElements as $addressElement) {

                $address = $this->cleanString($addressElement->innertext());
                if ($this->saveAddress($da, $address)) {
                    $addedAddresses++;
                }
            }
        }

        return ($addedAddresses > 0);

    }

    protected function extractApplicants($html, $da, $params = null): bool {

        $addedApplicants = 0;
        $legendElement = $html->find("legend");

        foreach ($legendElement as $legendElement) {

            $legendText = $this->cleanString($legendElement->innertext());
            if (strpos(strtolower($legendText), "applicant") === false) {
                continue;
            }

            $fieldsetElement = $legendElement->parent();
            if ($fieldsetElement === null) {
                continue;
            }

            $fieldsetHtml = str_get_html($fieldsetElement->innertext());
            if ($fieldsetHtml === false) {
                continue;
            }

            $applicantElements = $fieldsetHtml->find("[class=ContentText],[class=AlternateContentText]");
            foreach ($applicantElements as $applicantElement) {

                $role = "Applicant";
                $name = $this->cleanString($applicantElement->innertext());

                if (strlen($name) > 0 && $this->saveParty($da, $role, $name)) {
                    $addedApplicants++;
                }
            }
        }

        return ($addedApplicants > 0);

    }

    protected function extractDescription($html, $da, $params = null): bool {

        $headerElements = $html->find("[class=AlternateContentHeading]");
        foreach ($headerElements as $headerElement) {

            $headerText = $this->cleanString($headerElement->innertext());
            if (strpos(strtolower($headerText), "description") === false) {
                continue;
            }

            $spanElement = $headerElement->next_sibling();
            if ($spanElement === null) {
                return false;
            }

            $valueElement = $spanElement->children(0);
            if ($valueElement === null) {
                return false;
            }

            $value = $this->cleanString($valueElement->innertext());
            return (strlen($value) > 0 && $this->saveDescription($da, $value));
        }

        return false;

    }

    protected function extractOfficers($html, $da, $params = null): bool {

        $headerElements = $html->find("[class=AlternateContentHeading]");
        foreach ($headerElements as $headerElement) {

            $headerText = $this->cleanString($headerElement->innertext());
            if (strpos(strtolower($headerText), "officer") === false) {
                continue;
            }

            // Their HTML is badly formatted, and for some reason there's an orphan <td>
            $valueElement = $headerElement->next_sibling();
            if ($valueElement === null) {
                return false;
            }

            if ($valueElement->tag !== "div") {
                $valueElement = $valueElement->children(0);

                if ($valueElement === null) {
                    continue;
                }
            }

            $role = "Officer";
            $name = $this->cleanString($valueElement->innertext());

            return (strlen($name) > 0 && $this->saveParty($da, $role, $name));
        }

        return false;

    }


    public function extractLodgeDate($html, $da){
        echo "extracting lodge date....<br>";

        $headerElements = $html->find("[class=AlternateContentHeading]");
        foreach ($headerElements as $headerElement) {

            $headerText = $this->cleanString($headerElement->innertext());
            if (strpos(strtolower($headerText), "lodge") === false) {

                continue;
            }

            $valueElement = $headerElement->next_sibling();
            if ($valueElement === null) {
                return false;
            }

            $value = $this->cleanString($valueElement->plaintext);
            echo 'Date: '. $value . '<br>';
            $date = \DateTime::createFromFormat("d/m/Y", $value);



            return $this->saveLodgeDate($da, $date);
        }

        return false;
    }



    protected function extractDocuments($html, $da, $params = null): bool {
        echo "extracting documents.... <br>";
        $addedDocuments = 0;
        $url = sprintf("https://documents.campbelltown.nsw.gov.au/dwroot/datawrks/views/application/%s/links", $da->getCouncilReferenceAlt());

        echo $url . '<br>';
        $html = str_get_html($this->scrapeTo($url));
        if (!$html) {

            $logMsg = "Could not parse HTML";
            $this->logger->info($logMsg);
            return false;
        }

        $anchorElements = $html->find("a");
        foreach ($anchorElements as $anchorElement) {

            if (!isset($anchorElement->href) || strpos($anchorElement->id, "classabbrev") === false) {
                continue;
            }

            $regexPattern = '/\/viewDocument\?docid=([0-9]+)/';
            if (preg_match($regexPattern, $anchorElement->href, $matches) !== 0) {

                $documentName = $this->cleanString($anchorElement->innertext());
                $documentUrl = "https://documents.campbelltown.nsw.gov.au" . $this->cleanString($anchorElement->href);
                $documentDate = null;

                // Find document date, documents page is very badly structured
                $dateElement = $html->find("a[id=docdate." . $matches[1] . "]", 0);
                if ($dateElement !== null) {
                    $documentDate = \DateTime::createFromFormat("d/m/Y", $this->cleanString($dateElement->innertext()));
                }

                if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                    $addedDocuments++;
                }
            }
        }

        return ($addedDocuments > 0);
    }

}