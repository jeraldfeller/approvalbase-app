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
        $this->extractPeople($html, $da);
        $this->extractLodgeDate($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractOfficers($html, $da);
        $this->extractEstimatedCost($html, $da);
    }

    protected function extractAddresses($html, $da, $params = null): bool {

        $addedAddresses = 0;
        $addressElements = $html->find("a[title=Click to display property details]");

        foreach ($addressElements as $addressElement) {

            $daAddress = $this->cleanString($addressElement->innertext());
            if ($this->saveAddress($da, $daAddress) === true) {
                $addedAddresses++;
            }
        }

        return ($addedAddresses > 0);

    }

    protected function extractLodgeDate($html, $da, $params = null): bool {

        $detailsElement = $html->find("div[id=b_ctl00_ctMain_info_app]", 0);

        if ($detailsElement === null) {
            echo "Could not find details element. Lodge date may be incorrect.<br>";
        }
        else {

            $content = $this->cleanString($detailsElement->innertext());
            $regexPattern = '/Lodged: ([0-9]{2}\/[0-9]{2}\/[0-9]{4})/';

            if (preg_match($regexPattern, $content, $matches) === 0) {
                $logMsg = "Could not find lodge date. Lodge date may be incorrect.";
                echo $logMsg;
            }
            else {
                $oldLodgeDate = $da->getLodgeDate();
                $newLodgeDate = \DateTime::createFromFormat("d/m/Y", $matches[1]);
                return $this->saveLodgeDate($da, $newLodgeDate);
            }
        }

        return false;

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        $detailsElement = $html->find("div[id=b_ctl00_ctMain_info_app]", 0);
        if ($detailsElement === null) {

            return false;
        }

        $content = $this->cleanString($detailsElement->innertext());
        $regexPattern = '/Estimated Cost of Work:\s+\$ (.+)\s/';

        if (preg_match($regexPattern, $content, $matches) === 0) {

            return false;
        }

        return $this->saveEstimatedCost($da, $matches[1]);

    }

    protected function extractOfficers($html, $da, $params = null): bool {

        $addedOfficers = 0;
        $detailsElement = $html->find("div[id=b_ctl00_ctMain_info_app]", 0);

        if ($detailsElement !== null) {

            $content = $this->cleanString($detailsElement->innertext());
            $regexPattern = '/Officer:\s+(.+)\s?/';

            if (preg_match($regexPattern, $content, $matches) !== 0) {

                $role = "Officer";
                $name = $this->cleanString($matches[1]);

                if ($this->saveParty($da, $role, $name)) {
                    $addedOfficers++;
                }
            }
        }

        return ($addedOfficers > 0);

    }

    protected function extractPeople($html, $da, $params = null): bool {

        $addedPeople = 0;
        $peopleElement = $html->find("div[id=b_ctl00_ctMain_info_party]", 0);

        if ($peopleElement === null) {

            return false;
        }
        else {

            $rolesAndPersonsString = $this->cleanString($peopleElement->innertext());
            $rolesAndPersonsArray = explode("<br />", $rolesAndPersonsString);

            foreach ($rolesAndPersonsArray as $roleAndPersonString) {

                $roleAndPersonArray = explode("-", $roleAndPersonString);
                if (!isset($roleAndPersonArray[1])) {
                    continue;
                }

                $role = $this->cleanString($roleAndPersonArray[0]);
                $name = $this->cleanString($roleAndPersonArray[1]);

                if (strlen($name) > 0) {

                    if ($this->saveParty($da, $role, $name) === true) {
                        $addedPeople++;
                    }
                }
            }
        }

        return ($addedPeople > 0);

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