<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/26/2019
 * Time: 10:55 AM
 */

namespace Aiden\Models;


class SutherlandTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
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

    protected function extractDocuments($html, $da, $params = null): bool {

        $addedDocuments = 0;

        $anchorElements = $html->find("a");
        foreach ($anchorElements as $anchorElement) {

            $href = $this->cleanString($anchorElement->href);

            $regexPattern = '/webservice\.ssc\.nsw\.gov\.au\/ETrack\/default\.aspx\?page=dms&ctr=([0-9]+)&id=(.+)/';
            if (preg_match($regexPattern, $href, $matches) === 0) {
                continue;
            }

            $documentsUrl = "https://webservice.ssc.nsw.gov.au/ETrack/default.aspx"
                . "?page=dms"
                . "&ctr=" . $matches[1]
                . "&id=" . $matches[2];

            echo $documentsUrl . '<br>';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $documentsUrl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->config->dev);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$this->config->dev);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->directories->cookiesDir . 'cookies.txt');
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->directories->cookiesDir . 'cookies.txt');
            curl_setopt($ch, CURLOPT_USERAGENT, $this->config->useragent);

            $output = curl_exec($ch);
            $errno = curl_errno($ch);
            $errmsg = curl_error($ch);

            curl_close($ch);

            if ($errno !== 0) {
                $this->logger->error("cURL error: {errmsg} ({errno})", ["errmsg" => $errmsg, "errno" => $errno]);
                return false;
            }

            $docsRegexPattern = '/(([0-9]{1,2}\/[0-9]{2}\/[0-9]{4}) -\s+<a href="(.+?)" target="_blank" ?>(.+?)<\/a>)/';
            if (preg_match_all($docsRegexPattern, $output, $matches) === 1) {

                for ($i = 0; $i < count($matches[0]); $i++) {

                    $date = $this->cleanString($matches[2][$i]);
                    $documentDate = \DateTime::createFromFormat("d/m/Y", $date);

                    $documentUrl = $this->cleanString($matches[3][$i]);
                    $documentName = $this->cleanString($matches[4][$i]);

                    if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                        $addedDocuments++;
                    }
                }
            }
        }

        return ($addedDocuments > 0);

    }
}