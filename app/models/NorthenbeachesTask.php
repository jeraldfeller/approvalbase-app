<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/25/2019
 * Time: 11:10 AM
 */

namespace Aiden\Models;


class NorthenbeachesTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
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


    public function getData($html, $da){
        // get estimated cost
        $this->extractEstimatedCost($html, $da);
        return true;
    }

    protected function extractDocuments($html, $da, $params = null): bool {

        $addedDocuments = 0;
        $anchorElements = $html->find("a");
        $regexPattern = '/Common\/Output\/Document\.aspx\?id=/';

        foreach ($anchorElements as $anchorElement) {

            if (isset($anchorElement->href) === false) {
                continue;
            }

            if (preg_match($regexPattern, $anchorElement->href) === 0) {
                continue;
            }

            $firstChildElement = $anchorElement->children(0);
            if ($firstChildElement !== null && $firstChildElement->tag === "img") {
                continue;
            }

            $documentName = $this->cleanString(strip_tags($anchorElement->innertext()));

            $documentUrl = $this->cleanString($anchorElement->href);
            $documentUrl = str_replace("../../", "/", $documentUrl);
            $documentUrl = "https://eservices.northernbeaches.nsw.gov.au/ePlanning/live" . $documentUrl;

            $documentDate = null;

            $parentCellElement = $anchorElement->parent();
            if ($parentCellElement !== null) {

                $dateParentElement = $anchorElement->next_sibling();
                if ($dateParentElement !== null) {

                    $dateElement = $dateParentElement->children(0);
                    if ($dateElement !== null) {

                        $documentDateString = $this->cleanString($dateElement->innertext());
                        $documentDate = \DateTime::createFromFormat("d/m/Y", $documentDateString);
                    }
                }
            }

            if ($this->saveDocument($da, $documentName, $documentUrl)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $keyElements = $html->find("[class=detailleft]");
        foreach ($keyElements as $keyElement) {


            $keyText = $this->cleanString($keyElement->innertext());

            if (preg_match("/cost of work/i", $keyText) === 0) {
                continue;
            }

            $valueElement = $keyElement->next_sibling();
            if ($valueElement === null) {
                continue;
            }

            $value = $this->cleanString($valueElement->innertext());
            return $this->saveEstimatedCost($da, $value);
        }


        return false;

    }

}