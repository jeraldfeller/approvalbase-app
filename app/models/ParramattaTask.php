<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/25/2019
 * Time: 1:31 PM
 */

namespace Aiden\Models;


class ParramattaTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getCouncilUrl() . '<br>';
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


    public function extractData($html, $da)
    {
        $this->extractEstimatedCost($html, $da);
        return true;
    }

    protected function extractDocuments($html, $da, $params = null): bool {

        $addedDocuments = 0;
        $documentContainerElement = $html->find("[id=b_ctl00_ctMain_info_docs]", 0);

        if ($documentContainerElement === null) {
            return false;
        }

        $docsHtml = $html;
        if ($docsHtml === false) {
            return false;
        }

        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            $regexPattern = '/Common\/Output\/Document\.aspx\?id=/';
            if (preg_match($regexPattern, $anchorElement->href) === 0) {
                continue;
            }

            $documentUrl = str_replace("../../", "/", $anchorElement->href);
            $documentUrl = "http://eplanning.parracity.nsw.gov.au" . $documentUrl;

            $parentElement = $anchorElement->parent();
            if ($parentElement === null) {
                continue;
            }

            $documentNameElement = $parentElement->next_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString($documentNameElement->innertext());

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

        $detailsElement = $html->find("div[id=b_ctl00_ctMain_info_app]", 0);
        if ($detailsElement === null) {
            return false;
        }

        $content = $this->cleanString($detailsElement->innertext());
        $regexPattern = '/Estimated Cost of Work:\s+\$ (.+)\s/';

        if (preg_match($regexPattern, $content, $matches) === 0) {
            return false;
        }

        return $this->saveEstimatedCost($da, $matches[1]);

    }
}