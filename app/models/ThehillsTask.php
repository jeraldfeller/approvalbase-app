<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/26/2019
 * Time: 11:35 AM
 */

namespace Aiden\Models;


class ThehillsTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
        $url = "https://epathway.thehills.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx";
        $requiredFormData = $this->enquiryStep1($url);
        if ($requiredFormData === false) {
            return false;
        }
        $dateStart = new \DateTime("-30 days");
        $dateEnd = new \DateTime();
        $startDate = $dateStart->format("d/m/Y");
        $endDate = $dateEnd->format("d/m/Y");
        $output = $this->enquiryStep2($requiredFormData, $startDate, $endDate);
        if ($output === false) {
            return false;
        }
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
     * Tells the server we're looking for development applications
     */
    public function enquiryStep1($url) {



        $formData = $this->getAspFormDataByUrl($url);
        $formData['ctl00$MainBodyContent$mContinueButton'] = "Next";
        $formData['ctl00$mHeight'] = 653;
        $formData['ctl00$mWidth'] = 786;
        $formData['mDataGrid:Column0:Property'] = 'ctl00$MainBodyContent$mDataList$ctl03$mDataGrid$ctl02$ctl00';
        $formData['__LASTFOCUS'] = null;
        $formData = http_build_query($formData);

        $requestHeaders = [
            "Host: epathway.thehills.nsw.gov.au",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-GB,en;q=0.5",
            "Accept-Encoding: none",
            "Referer: https://epathway.thehills.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx",
            "Content-Type: application/x-www-form-urlencoded",
            "Connection: keep-alive",
            "dnt: 1",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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

            $message = "cURL error in enquiryStep1 function: " . $errmsg . " (" . $errno . ")";
            echo $message . '<br>';
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
    public function enquiryStep2($formData, $startDate, $endDate) {



        $url = "https://epathway.thehills.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquirySearch.aspx";

        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mEnquiryListsDropDownList'] = 7;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mSearchButton'] = "Search";
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$DateSearchRadioGroup'] = "mLast30RadioButton";
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mFromDatePicker$dateTextBox'] = $startDate;
        $formData['ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mToDatePicker$dateTextBox'] = $endDate;
        $formData['ctl00$mHeight'] = 653;
        $formData['ctl00$mWidth'] = 786;

        $formData = http_build_query($formData);

        $requestHeaders = [
            "Host: epathway.thehills.nsw.gov.au",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-GB,en;q=0.5",
            "Accept-Encoding: none",
            "Referer: https://epathway.thehills.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx",
            "Content-Type: application/x-www-form-urlencoded"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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

            $message = "cURL error in enquiryStep2 function: " . $errmsg . " (" . $errno . ")";
            echo $message . '<br>';
            return false;
        }

        return $output;

    }


    protected function extractDocuments($html, $da, $params = null): bool {

        $regexPattern = '/\/ApplicationTracking\/Document\/View\?key=/';
        $addedDocuments = 0;
        $url = "https://apps.thehills.nsw.gov.au/ApplicationTracking/Application/Documents/" . $da->getCouncilReferenceAlt();

        echo $url . '<br>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
            $this->logger->error("cURL error: {errmsg} ({errno})", ["errmsg" => $errmsg, "errno" => $errno]);
            return false;
        }

        $docsHtml = str_get_html($output);
        if (!$docsHtml) {
            $this->logger->error("Could not parse HTML");
            return false;
        }

        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            $documentUrl = "https://apps.thehills.nsw.gov.au";
            $documentUrl .= $this->cleanString($anchorElement->href);

            $urlParentElement = $anchorElement->parent();
            if ($urlParentElement === null) {
                continue;
            }

            $documentDateElement = $urlParentElement->prev_sibling();
            if ($documentDateElement === null) {
                continue;
            }

            $documentDate = $this->cleanString($documentDateElement->innertext());
            $documentDate = \DateTime::createFromFormat("j/m/Y", $documentDate);

            $documentNameElement = $documentDateElement->prev_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString($documentNameElement->innertext());

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);

    }

}