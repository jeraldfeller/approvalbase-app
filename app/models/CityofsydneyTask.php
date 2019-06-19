<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/19/2019
 * Time: 12:48 PM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class CityofsydneyTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {
        $html = $this->scrapeTo($da->getCouncilUrl());
        echo "URL: " . $da->getCouncilUrl() . '<br>';
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
        $documentsElement = $html->find("div[id=documents_info]", 0);

        if ($documentsElement === null) {
            return false;
        }

        $ulElement = $documentsElement->children(0);
        if ($ulElement === null) {
            return false;
        }

        $documentListElements = $ulElement->children();
        foreach ($documentListElements as $documentListElement) {

            $anchorElement = $documentListElement->children(0);
            if ($anchorElement === null) {
                continue;
            }

            $documentUrl = $this->cleanString($anchorElement->href);
            $documentName = $this->cleanString($anchorElement->innertext());

            if ($this->saveDocument($da, $documentName, $documentUrl)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);
    }
}