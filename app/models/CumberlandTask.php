<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/19/2019
 * Time: 1:40 PM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;
class CumberlandTask extends  _BaseModel
{

    public function init($actions, $da, $method ='get')
    {
        echo $da->getCouncilUrl() . '<br>';
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

        // <div id="edms">
        $documentsContainerElement = $html->find("div[id=edms]", 0);
        if ($documentsContainerElement === null) {
            return false;
        }

        // <div class="details">
        $detailElement = $documentsContainerElement->children(0);
        if ($detailElement) {
            return false;
        }

        // <div class="detailright"the 2nd child of <div class="detail">, the 1st child is hidden.
        $detailRightElement = $detailElement->children(1);
        if ($detailRightElement === null) {
            return false;
        }

        // <table> is the 1st childof <div class="detailright">
        $tableElement = $detailRightElement->children(0);
        if ($tableElement === null) {
            return false;
        }

        // Each of $tableElements children contains a document
        foreach ($tableElement->children() as $tableRowElement) {

            $firstTd = $tableRowElement->children(0); // Contains the URL
            $secondTd = $tableRowElement->children(1); // Contains the Name
            $thirdTd = $tableElement->children(2); // Contains the Date

            if ($firstTd === null || $secondTd === null || $thirdTd === null) {
                continue;
            }

            $anchorElement = $firstTd->children(0);
            if ($anchorElement === null) {
                continue;
            }

            // Generate Document URL
            $documentUrl = $this->cleanString($anchorElement->href);
            $documentUrl = str_replace("../../", "/", $documentUrl);
            $documentUrl = "http://eplanning.cumberland.nsw.gov.au" . $documentUrl;

            $documentName = $this->cleanString($secondTd->innertext());
            $documentDate = \DateTime::createFromFormat("r", $this->cleanString($thirdTd->innertext()));

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);
    }

}