<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/21/2019
 * Time: 10:37 AM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;
class GeorgestiverTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getCouncilUrl() . '<br>';
        $url = "http://daenquiry.georgesriver.nsw.gov.au/masterviewui/Modules/applicationmaster/default.aspx?page=wrapper&key=177552";
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

    public function acceptTerms($formData) {

        $url = "http://daenquiry.georgesriver.nsw.gov.au/masterviewui/Modules/applicationmaster/Default.aspx";

        $formData["__EVENTTARGET"] = null;
        $formData["__EVENTARGUMENT"] = null;
        $formData['ctl00$cphContent$ctl00$Button1'] = "Agree";
        $formData = http_build_query($formData);

        $requestHeaders = [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Encoding: none",
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: " . strlen($formData),
            "Host: daenquiry.georgesriver.nsw.gov.au",
            "Origin: http://daenquiry.georgesriver.nsw.gov.au"
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);

        if ($errno !== 0) {
            echo "cURL error: $errmsg  $errno <br>";
            return false;
        }

    }


    protected function extractDocuments($html, $da, $params = null): bool {
        echo "Extacting Documents: <br>";
        $addedDocuments = 0;
        $documentsElement = $html->find("[id=lblDocuments]", 0);

        if ($documentsElement === null) {
            return false;
        }

        $regexPattern = '/\x{2666}\s([A-Z]{1}[0-9]{2}\/[0-9]{6})\s(.+?) ([0-9]{2}\/[0-9]{2}\/[0-9]{4})/u';
        $content = $documentsElement->plaintext;

        $anchorElements = [];
        foreach ($documentsElement->children() as $potentialAnchorElement) {

            if ($potentialAnchorElement->tag !== "a") {
                continue;
            }

            $anchorElements[] = $potentialAnchorElement;
        }

        if (preg_match_all($regexPattern, $content, $matches) !== 0) {

            $amountOfDocuments = count($matches[0]);
            for ($i = 0; $i < $amountOfDocuments; $i++) {

                $documentName = $this->cleanString($matches[1][$i] . " " . $matches[2][$i]);
                $documentDate = \DateTime::createFromFormat("d/m/Y", $matches[3][$i]);

                $documentUrl = $this->cleanString($anchorElements[$i]->href);
                $documentUrl = str_replace("../../", "/", $documentUrl);
                $documentUrl = "http://daenquiry.georgesriver.nsw.gov.au/masterviewui" . $documentUrl;

                if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate) === true) {
                    $addedDocuments++;
                }
            }
        }

        return ($addedDocuments > 0);
    }


}