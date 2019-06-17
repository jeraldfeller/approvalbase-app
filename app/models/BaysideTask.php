<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/17/2019
 * Time: 8:43 AM
 */

namespace Aiden\Models;

use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class BaysideTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'documents':
                    $this->extractDocuments("", $da);
                    break;
            }
        }
    }


    protected function extractDocuments($html, $da, $params = null): bool {
        $addedDocuments = 0;
        $url = $da->getCouncilUrl();
        echo 'URL: ' . $url . '<br>';
        $html = $this->scrapeTo($url);
        $filesContainer = $html->find('.file');
        foreach($filesContainer as $file){
            $a = $file->find('a', 1);
            if($a){
                $documentName = $this->cleanString($a->innertext());
                $documentUrl = 'https://eplanning.bayside.nsw.gov.au/ePlanning/'.str_replace('../../', '', $a->getAttribute('href'));
                if ($this->saveDocument($da, $documentName, $documentUrl)) {
                    $addedDocuments++;
                }
            }
        }

        return ($addedDocuments > 0);
    }

}