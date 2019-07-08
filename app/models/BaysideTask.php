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

        echo $da->getCouncilUrl() . '<br>';
        $html = $this->scrapeTo($da->getCouncilUrl());
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'data':
                    $this->getData($html, $da);
                    break;
                case 'documents':
                    $this->extractDocuments("", $da);
                    break;
            }
        }
    }

    public function getData($html, $da)
    {
        $this->extractLodgeDate($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractEstimatedCost($html, $da);
        $this->extractDescription($html, $da);
        $this->extractApplicants($html, $da);
        $this->extractOfficers($html, $da);
    }

    protected function extractAddresses($html, $da, $params = null): bool
    {

        $addedAddresses = 0;

        $addressElement = $html->find("strong");
        if ($addressElement === null) {

            return false;
        }

        foreach ($addressElement as $add) {
            $address = $this->cleanString($add->innertext());


            if ($address) {
                if ($this->saveAddress($da, $address) === true) {
                    $addedAddresses++;
                }
            }
        }


        return ($addedAddresses > 0);

    }

    protected function extractApplicants($html, $da, $params = null): bool
    {
        $removeThis = '<!--../../Pages/XC.Track.East.Party/SearchParty.aspx?id={PartyId}-->';
        $addedApplicant = 0;
        $url = $da->getCouncilUrl();
        $html = $this->scrapeTo($url);
        $detail = $html->find('.detail', 2);
        $detailright = $detail->find('.detailright', 0);
        $containerText = $this->cleanString($detailright->innertext());
        $peopleParts = explode('<br />', $containerText);

        for($x = 0; $x < count($peopleParts); $x++){
            if($peopleParts[$x] != ''){
                $containerParts = explode('-', str_replace($removeThis, '', $peopleParts[$x]));
                $role = trim($containerParts[0]);

                if(strpos($role, 'span') === false){
                    if(strtolower($role) != 'officer'){
                        $applicant = trim($containerParts[1]);
                        if ($this->saveParty($da, $role, $applicant)) {
                            $addedApplicant++;
                        }
                    }
                }

            }
        }

        return ($addedApplicant > 0);

    }

    protected function extractDescription($html, $da, $params = null): bool
    {

        $url = $da->getCouncilUrl();
        $html = $this->scrapeTo($url);
        $detail = $html->find('.detail', 0);
        $detailright = $detail->find('.detailright', 0);
        $container = $detailright->find('div', 0);
        if($container == null){
            return false;
        }
        $value = $this->cleanString($container->innertext());
        return $this->saveDescription($da, $value);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool
    {
        $url = $da->getCouncilUrl();
        $html = $this->scrapeTo($url);
        $detail = $html->find('.detail', 0);
        $detailright = $detail->find('.detailright', 0);
        $estimatedCostContainer = $detailright->find('div', 3);
        $value = $this->cleanString($estimatedCostContainer->innertext());
        return $this->saveEstimatedCost($da, $value);

    }

    protected function extractLodgeDate($html, $da, $params = null): bool
    {
        $url = $da->getCouncilUrl();
        $html = $this->scrapeTo($url);
        $detail = $html->find('.detail', 0);
        $detailright = $detail->find('.detailright', 0);
        $lodgeDateContiner = $detailright->find('div', 2);

        $value = $this->cleanString($lodgeDateContiner->innertext());
        $dateParts = str_replace("Lodged: ", "", $value);

        $date = \DateTime::createFromFormat("d/m/Y", $dateParts);
        return $this->saveLodgeDate($da, $date);

    }

    protected function extractOfficers($html, $da, $params = null): bool
    {
        $addedOfficers = 0;
        $url = $da->getCouncilUrl();
        $html = $this->scrapeTo($url);
        $detail = $html->find('.detail', 0);
        $detailright = $detail->find('.detailright', 0);
        $container = $detailright->find('div', 4);
        $containerText = $this->cleanString($container->innertext());
        $containerParts = explode(':', $containerText);
        $role = $containerParts[0];
        if(strtolower($role) == 'officer'){
            $officer = trim($containerParts[1]);
            if ($this->saveParty($da, $role, $officer)) {
                $addedOfficers++;
            }
        }

        return ($addedOfficers > 0);
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
                $date = null;
                $td = $file->find('td', 2);
                if($td){
                    $dateStr = $this->cleanString($td->innertext());
                    $documentDate = \DateTime::createFromFormat("d/m/Y", $dateStr);

                }
                $documentName = $this->cleanString($a->innertext());
                $documentUrl = 'https://eplanning.bayside.nsw.gov.au/ePlanning/'.str_replace('../../', '', $a->getAttribute('href'));
                if ($this->saveDocument($da, $documentName, $documentUrl, $documentDate)) {
                    $addedDocuments++;
                }
            }
        }

        return ($addedDocuments > 0);
    }

}