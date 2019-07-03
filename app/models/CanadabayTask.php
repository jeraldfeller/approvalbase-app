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
                case 'data':
                    $this->extractData($html, $da);
                    break;
                case 'documents':
                    $this->extractDocuments($html, $da);
                    break;
            }
        }
    }

    public function extractData($html, $da)
    {
        $this->extractPeople($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractDescription($html, $da);
        $this->extractLodgeDate($html, $da);
        $this->extractEstimatedCost($html, $da);
        return true;
    }

    protected function extractAddresses($html, $da, $params = null): bool {

        $addedAddresses = 0;
        $container = $html->find('#addr', 0);
        $addresses = $container->find('.detailright');
        foreach ($addresses as $row){
            $a = $row->find('a', 0);
            if($a){
                $address = $this->cleanString($a->innertext());
                if ($this->saveAddress($da, $address) === true) {
                    $addedAddresses++;
                }
            }
        }
        return ($addedAddresses > 0);

    }

    protected function extractPeople($html, $da, $params = null): bool {

        $addedParties = 0;
        $container = $html->find('#ppl', 0);
        if($container){
            $details = $container->find('.detailright');
            if($details){
                foreach ($details as $d){
                    $detail = $d->innertext();
                    $detailArr = explode("<br />", $detail);
                    for($x = 0; $x < count($detailArr); $x++){
                        $rolePeople = explode('-', $detailArr[$x]);
                        $role = $this->cleanString($rolePeople[0]);
                        if($role != ''){
                            $name = $this->cleanString($rolePeople[1]);

                            if ($this->saveParty($da, $role, $name)) {
                                $addedParties++;
                            }
                        }
                    }
                }
            }
        }
        return ($addedParties > 0);

    }

    protected function extractDescription($html, $da, $params = null): bool {


        $container = $html->find('#b_ctl00_ctMain_info_app', 0);
        if($container){
            $info = $container->innertext();
            $infoArr = explode('<br />', $info);
            $description = $this->cleanString($infoArr[0]);
            if (strlen($description) > 0) {
                return $this->saveDescription($da, $description);
            }
        }

        return false;

    }

    protected function extractLodgeDate($html, $da, $params = null): bool {

        $container = $html->find('#b_ctl00_ctMain_info_app', 0);
        if($container){
            $info = $container->innertext();
            $infoArr = explode('<br />', $info);
            for($x = 0; $x < count($infoArr); $x++){

                if(strpos($infoArr[$x], 'Lodged') !== false){
                    $lodgeInfo = explode(':', $infoArr[$x]);
                    $lodgeDate = $this->cleanString(($lodgeInfo[1] != '' ? $lodgeInfo[1] : false));
                    if($lodgeDate){
                        $date = \DateTime::createFromFormat("d/m/Y", $lodgeDate);
                        return $this->saveLodgeDate($da, $date);
                    }

                }
            }
        }
        return false;

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {
        $container = $html->find('#b_ctl00_ctMain_info_app', 0);
        if($container){
            $info = $container->innertext();
            $infoArr = explode('<br />', $info);
            for($x = 0; $x < count($infoArr); $x++){

                if(strpos($infoArr[$x], 'Estimated Cost') !== false){
                    $costInfo = explode(':', $infoArr[$x]);
                    $costInfo = $this->cleanString(($costInfo[1] != '' ? $costInfo[1] : false));
                    if($costInfo){
                        return $this->saveEstimatedCost($da, $costInfo);
                    }

                }
            }
        }
        return false;

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