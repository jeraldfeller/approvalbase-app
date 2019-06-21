<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/21/2019
 * Time: 11:16 AM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class HornsbyshireTask  extends  _BaseModel
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

    protected function extractDocuments($html, $da, $params = null): bool
    {
        $addedDocuments = 0;
        $documentContainerElement = $html->find("[id=b_ctl00_ctMain_info_docs]", 0);

        if ($documentContainerElement === null) {
            return false;
        }

        $docsHtml = str_get_html($documentContainerElement->innertext());
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
            $documentUrl = "http://hscenquiry.hornsby.nsw.gov.au" . $documentUrl;

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

}