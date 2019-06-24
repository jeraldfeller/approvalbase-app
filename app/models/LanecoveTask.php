<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/24/2019
 * Time: 2:37 PM
 */

namespace Aiden\Models;


class LanecoveTask extends _BaseModel
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

        $docsHtml = $html;

        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            $regexPattern = '/(http:\/\/ecouncil\.lanecove\.nsw\.gov\.au\/TRIM\/documents_TE\/.+?\/)(.+)/';
            if (preg_match($regexPattern, $anchorElement->href, $matches) === 0) {
                continue;
            }

            $documentUrl = $this->cleanString($anchorElement->href);

            // Lane cove is still in 1995
            $fontElement = $anchorElement->parent();
            if ($fontElement === null) {
                continue;
            }

            $parentCellElement = $fontElement->parent();
            if ($parentCellElement === null) {
                continue;
            }

            $documentNameElement = $parentCellElement->prev_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString(strip_tags($documentNameElement->innertext()));

            // Try to parse date, it doesn't matter if we can't.
            $documentDateElement = $documentNameElement->prev_sibling();
            if ($documentDateElement !== null) {

                $documentDateString = $this->cleanString(strip_tags($documentDateElement->innertext()));
                $documentDate = \DateTime::createFromFormat("d/m/Y g:i:s A", $documentDateString);
            }

            if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate) === true) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);

    }
}