<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/19/2019
 * Time: 11:32 AM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class CanterburyBankstownTask extends _BaseModel
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

        $container = $html->find('#Documents', 0);
        if($container){
            $table = $container->find('table', 0);
            if($table){
                $tr = $table->find('tr', 0);
                if($tr){
                    $td = $tr->find('td', 0);
                    if($td){
                        $a = $td->find('a');
                        foreach($a as $row){
                            $date =  new \DateTime();
                            $documentUrl = $this->cleanString($row->getAttribute('href'));
                            $documentName = $this->cleanString($row->plaintext);
                            if ($this->saveDocument($da, $documentName, $documentUrl, $date)) {
                                $addedDocuments++;
                            }

                        }
                    }
                }
            }
        }

        return ($addedDocuments > 0);

    }
}