<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/28/2019
 * Time: 10:15 AM
 */

namespace Aiden\Models;


class WoollahraTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
        $this->acceptTerms();
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
    public function acceptTerms()
    {

        $url = "https://eservices.woollahra.nsw.gov.au/eservice/daEnquiryInit.do?nodeNum=5270";


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

            $message = "cURL error: " . $errmsg . " (" . $errno . ")";
            echo $message . '<br>';
            return false;
        }

    }

    protected function extractDocuments($html, $da, $params = null): bool
    {
        $addedDocuments = 0;
        $tables = $html->find('table');
        foreach ($tables as $table) {
            $summary = $table->getAttribute('summary');
            if($summary == 'Electronic Documents Associated this Development Application'){
                $tr = $table->find('tr');
                if($tr){
                    foreach($tr as $row){
                        $td = $row->find('td', 1);
                        if($td){
                            $a = $td->find('a', 0);
                            $documentName = $this->cleanString($a->innertext());
                            $documentUrl = 'https://eservices.woollahra.nsw.gov.au'.$a->getAttribute('href');
                            if ($this->saveDocument($da, $documentName, $documentUrl)) {
                                $addedDocuments++;
                            }
                        }
                    }
                }

            }
        }

        return true;

    }

}