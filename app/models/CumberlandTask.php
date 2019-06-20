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
        echo "Extracting Documents <br>";
        $addedDocuments = 0;

        // <div id="edms">
        $documentsContainerElement = $html->find("#edms", 0);
        if ($documentsContainerElement === null) {
            return false;
        }

        $table = $documentsContainerElement->find('table', 0);
        if($table){
            $tr = $table->find('tr');
            foreach($tr as $row){
                $linkContainer = $row->find('td', 0);
                if($linkContainer){
                    $anchorElement = $linkContainer->find('a', 0);
                    $documentUrl = $this->cleanString($anchorElement->href);
                    $documentUrl = str_replace("../../", "/", $documentUrl);
                    $documentUrl = "http://eplanning.cumberland.nsw.gov.au" . $documentUrl;

                    $nameContainer = $row->find('td', 1);
                    if($nameContainer){
                        $documentName = $this->cleanString($nameContainer->innertext());
                    }

                    $dateContainer = $row->find('td', 2);
                    if($dateContainer){
                        $documentDate = \DateTime::createFromFormat("l, d M Y H:i:s T", $this->cleanString($dateContainer->innertext()));
                        if($documentDate == false){
                            $documentDate = null;
                        }
                    }else{
                        $documentDate = null;
                    }


                    if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                        $addedDocuments++;
                    }
                }


            }
        }

        return ($addedDocuments > 0);
    }

}