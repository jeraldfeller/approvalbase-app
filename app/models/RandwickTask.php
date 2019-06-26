<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/26/2019
 * Time: 7:51 AM
 */

namespace Aiden\Models;


class RandwickTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

        echo $da->getCouncilUrl() . '<br>';
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


        $docsHtml = $html;
        if (!$docsHtml) {
            return false;
        }

        $anchorElements = $docsHtml->find("a");
        foreach ($anchorElements as $anchorElement) {

            if ($anchorElement->children(0) === null || $anchorElement->children(0)->tag !== "img") {
                continue;
            }

            $regexPattern = '/Common\/Integration\/FileDownload\.ashx\?id=/';
            if (preg_match($regexPattern, $anchorElement->href) === 0) {
                continue;
            }

            $documentUrl = str_replace("../../", "/", $anchorElement->href);
            $documentUrl = "http://planning.randwick.nsw.gov.au" . $documentUrl;

            $parentElement = $anchorElement->parent();
            if ($parentElement === null) {
                continue;
            }

            $documentNameElement = $parentElement->next_sibling();
            if ($documentNameElement === null) {
                continue;
            }

            $documentName = $this->cleanString(strip_tags($documentNameElement->innertext()));

            if ($this->saveDocument($da, $documentName, $documentUrl)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $container = $html->find('.detail', 0);
        if($container){
            $detailRight = $container->find('.detailright', 0);
            if($detailRight){
                $detail = explode('<br />', $detailRight->innertext());
                for($i = 0; $i < count($detail); $i++){
                    if(strpos($detail[$i], 'Estimated Cost of Work') !== false){
                        $info = explode(':', $detail[$i]);
                        if(count($info) > 1){
                            $header = trim($info[0]);
                            $value = trim($info[1]);
                            $estimatedCost = $this->cleanString($value);
                            return $this->saveEstimatedCost($da, $estimatedCost);
                        }
                }
                }
            }
        }
        return false;
    }

}