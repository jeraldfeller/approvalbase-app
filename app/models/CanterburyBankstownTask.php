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
        $this->extractDescription($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractLodgeDate($html, $da);
        $this->extractPeople($html, $da);
    }

    protected function extractAddresses($html, $da, $params = null): bool {

        $addedAddresses = 0;
        $rows = $html->find(".row");
        foreach($rows as $row){
            $b = $row->find('b', 0);
            if($b){
                $tableHeader = trim($row->find('b', 0)->innerText());

                if($tableHeader == 'Address:'){

                    $addressElement = $row->find('div');
                    foreach ($addressElement as $add){
                        $address = $add->innerText();
                        if(strpos($address, 'Address') === false){
                            if ($this->saveAddress($da, $this->cleanString($address))) {
                                $addedAddresses++;
                            }
                        }
                    }
                }
            }
        }
        return ($addedAddresses > 0);
    }


    protected function extractLodgeDate($html, $da, $params = null): bool {

        $rows = $html->find(".row");
        foreach($rows as $row){
            $b = $row->find('b', 0);
            if($b){
                $tableHeader = trim($row->find('b', 0)->innerText());

                if($tableHeader == 'Lodged:'){

                    $element = $row->find('div', 1);
                    if($element){
                        $value = $element->innerText();
                        $value = explode(' ', $value);
                        $daLodgeDate = \DateTime::createFromFormat("d/m/Y", $value[0]);
                        return $this->saveLodgeDate($da,$daLodgeDate);
                    }
                }
            }
        }
    }

    protected function extractDescription($html, $da, $params = null): bool {

        $rows = $html->find(".row");
        foreach($rows as $row){
            $b = $row->find('b', 0);
            if($b){
                $tableHeader = trim($row->find('b', 0)->innerText());

                if($tableHeader == 'Description:'){

                    $element = $row->find('div', 1);
                    if($element){
                        $value = $element->innerText();

                        return $this->saveDescription($da, $this->cleanString($value));
                    }
                }
            }
        }

        return false;

    }

    protected function extractPeople($html, $da, $params = null): bool {

        $addedParties = 0;

        $container = $html->find('#People', 0);
        if($container){
            $table = $container->find('table', 0);
            if($table){
                $tr = $table->find('tr');
                foreach ($tr as $row){
                    $th = $row->find('th', 0);
                    if($th){
                        $header = trim($th->innerText());

                        $td = $row->find('td', 0);
                        if($td){
                            if($header == 'People:'){
                                $value = $td->innerText();
                                // get Applicants
                                $applicantValue = trim($this->get_string_between($value, 'Applicant:', 'Owner:'));
                                $applicantValueExplode = explode('<br />', $applicantValue);
                                for($i = 0; $i < count($applicantValueExplode); $i++){
                                    $name = trim($this->cleanString($applicantValueExplode[$i]));

                                    if($name != ''){
                                        if ($this->saveParty($da, 'Applicant', $name)) {
                                            $addedParties++;
                                        }
                                    }
                                }

                                // get Owners
                                if (($pos = strpos($value, "Owner:")) !== FALSE) {
                                    $owners = substr($value, $pos+6);
                                    $ownersExplode = explode('<br />', $owners);
                                    for($i = 0; $i < count($ownersExplode); $i++){
                                        $name = trim($this->cleanString($ownersExplode[$i]));

                                        if($name != ''){
                                            if ($this->saveParty($da, 'Owner', $name)) {
                                                $addedParties++;
                                            }
                                        }
                                    }

                                }
                            }else if($header == 'Officer(s):'){
                                $value = $td->innerText();
                                $officerValue = trim($value);
                                $officerValueExplode = explode('<br />', $officerValue);
                                for($i = 0; $i < count($officerValueExplode); $i++){
                                    $name = trim($this->cleanString($officerValueExplode[$i]));
                                    if($name != ''){
                                        if ($this->saveParty($da, 'Officer', $name)) {
                                            $addedParties++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return ($addedParties > 0);

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

    public function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}