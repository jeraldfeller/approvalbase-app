<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/25/2019
 * Time: 10:33 AM
 */

namespace Aiden\Models;



class MosmanTask extends _BaseModel
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
        $this->extractLodgeDate($html, $da);
        return true;
    }


    public function extractLodgeDate($html, $da){
        $headerElements = $html->find("[class=ndetailleft]");
        foreach ($headerElements as $headerElement) {

            $headerText = $this->cleanString($headerElement->innertext());
            if (strpos(strtolower($headerText), "submitted") === false) {
                continue;
            }

            $valueElement = $headerElement->next_sibling();
            if ($valueElement === null) {
                continue;
            }

            $value = $this->cleanString(strip_tags($valueElement->innertext()));
            $date = \DateTime::createFromFormat("d/m/Y", $value);
            return $this->saveLodgeDate($da, $date);
        }

        return false;
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
            $documentUrl = "https://portal.mosman.nsw.gov.au" . $documentUrl;

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

}