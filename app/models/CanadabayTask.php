<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/19/2019
 * Time: 10:26 AM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class CanadabayTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {

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
        $container = $html->find('#b_ctl00_ctMain_info_docs', 0);
        if($container){
            $table = $container->find('table', 0);
            if($table){
                $tr = $table->find('tr');
                foreach($tr as $row){
                    if($row->find('td', 0)->innertext() != ''){
                        $documentUrl = "http://datracking.canadabay.nsw.gov.au/".$this->cleanString(str_replace('../../', '', $row->find('td', 0)->find('a', 0)->getAttribute('href')));
                        $documentName = $this->cleanString($row->find('td', 1)->innertext());
                        $created = $this->cleanString($row->find('td', 2)->innertext());

                        echo $documentName . ' - ' . $documentUrl . "<br>";
                        if($created != ''){
                            $documentDate = \DateTime::createFromFormat("d/m/Y", $created);
                        }


                        if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                            $addedDocuments++;
                        }
                    }
                }
            }
        }
        return ($addedDocuments > 0);
    }
}