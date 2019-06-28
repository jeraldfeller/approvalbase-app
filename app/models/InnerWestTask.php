<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/24/2019
 * Time: 3:06 PM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class InnerWestTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'documents':
                    if(strpos($da->getCouncilUrl(), 'eservices.lmc.nsw.gov.au') !== false){
                        $this->extractDocumentsLeichhardt('', $da);
                    }else if(strpos($da->getCouncilUrl(), 'eproperty.marrickville.nsw.gov.au') !== false){
                        $this->extractDocumentsMarrickville('', $da);
                    }

                    break;
            }
        }
    }


    protected function extractDocumentsMarrickville($html, $da, $params = null): bool {

        $addedDocuments = 0;
        $url = "https://gotrim.marrickville.nsw.gov.au/WebGrid/default.aspx"
            . "?s=PlanningDocuments"
            . "&container=" . $da->getCouncilReference();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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

        $docsHtml = str_get_html($output);
        if (!$docsHtml) {

            return false;
        }

        $anchorElements = $docsHtml->find("a[title=Download]");
        foreach ($anchorElements as $anchorElement) {

            $documentUrl = $this->cleanString($anchorElement->href);

            // Document Date (used)
            $documentUrlParentElement = $anchorElement->parent();
            if ($documentUrlParentElement === null) {
                continue;
            }

            $documentDateElement = $documentUrlParentElement->prev_sibling();
            if ($documentDateElement === null) {
                continue;
            }

            $documentDateString = $documentDateElement->innertext();
            $documentDateStringParts = explode(" ", $documentDateString);
            $documentDate = \DateTime::createFromFormat("j/m/Y", $documentDateStringParts[0]);

            // Document File Size (unused)
            $documentSizeElement = $documentDateElement->prev_sibling();
            if ($documentSizeElement === null) {
                continue;
            }

            // Document Type (unused)
            $documentTypeElement = $documentSizeElement->prev_sibling();
            if ($documentTypeElement === null) {
                continue;
            }

            // Document Name (used)
            $documentNameElement = $documentTypeElement->prev_sibling();
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


    protected function extractDocumentsLeichhardt($html, $da, $params = null): bool {
        $addedDocuments = 0;
        $url = "http://eservices.lmc.nsw.gov.au/ApplicationTracking/Pages/XC.Track/Services/ECMConnectService.aspx/GetDocuments";
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
            $documentUrl = "http://eservices.lmc.nsw.gov.au/ApplicationTracking" . $documentUrl;

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);
    }

}