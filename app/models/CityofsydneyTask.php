<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/19/2019
 * Time: 12:48 PM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class CityofsydneyTask extends _BaseModel
{
    public function init($actions, $da, $method ='get')
    {
        $html = $this->scrapeTo($da->getCouncilUrl());
        echo "URL: " . $da->getCouncilUrl() . '<br>';
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

    public function extractData($html, $da){
        $this->extractDescription($html, $da);
        $this->extractEstimatedCost($html, $da);
        $this->extractAddresses($html, $da);
        $this->extractLodgeDate($html, $da);
        $this->extractOfficer($html, $da);
    }
    protected function extractDescription($html, $da, $params = null): bool {

        // Find estimated cost label
        $descriptionLabelElement = $html->find("label[for=Class_Description]", 0);
        if ($descriptionLabelElement === null) {
            return false;
        }

        // <td> containing this <label>
        $parentCellElement = $descriptionLabelElement->parent();
        if ($parentCellElement === null) {
            return false;
        }

        // <td> next to <label>'s parent
        $valueElement = $parentCellElement->next_sibling();
        if ($valueElement === null) {
            return false;
        }

        $value = $this->cleanString($valueElement->innertext());
        return $this->saveDescription($da, $value);

    }

    protected function extractLodgeDate($html, $da, $params = null): bool {

        // Find address label
        $lodgeDateLabelElement = $html->find("label[for=Lodged_Date]", 0);
        if ($lodgeDateLabelElement === null) {
            return false;
        }

        // <td> containing this <label>
        $parentCellElement = $lodgeDateLabelElement->parent();
        if ($parentCellElement === null) {
            return false;
        }

        // <td> next to <label>'s parent
        $valueElement = $parentCellElement->next_sibling();
        if ($valueElement === null) {
            return false;
        }

        $oldLodgeDate = $da->getLodgeDate();
        $newLodgeDate = \DateTime::createFromFormat("d/m/y", $this->cleanString($valueElement->innertext()));
        return $this->saveLodgeDate($da, $newLodgeDate);

    }

    protected function extractAddresses($html, $da, $params = null): bool {

        // Find address label
        $addressesLabelElement = $html->find("label[for=Addresses]", 0);
        if ($addressesLabelElement === null) {
            return false;
        }

        // <td> containing this <label>, label mentions addresses in plural, but so far
        // have only come across single addresses.
        $parentCellElement = $addressesLabelElement->parent();
        if ($parentCellElement === null) {
            return false;
        }

        // <td> next to <label>'s parent, contains the address value.
        $valueElement = $parentCellElement->next_sibling();
        if ($valueElement === null) {
            return false;
        }

        $daAddress = $this->cleanString($valueElement->innertext());
        return $this->saveAddress($da, $daAddress);

    }

    protected function extractEstimatedCost($html, $da, $params = null): bool {

        // Find estimated cost label
        $estimatedCostLabelElement = $html->find("label[for=Estimated_Cost]", 0);
        if ($estimatedCostLabelElement === null) {
            return false;
        }

        // <td> containing this <label>
        $parentCellElement = $estimatedCostLabelElement->parent();
        if ($parentCellElement === null) {
            return false;
        }

        // <td> next to <label>'s parent
        $valueElement = $parentCellElement->next_sibling();
        if ($valueElement === null) {
            return false;
        }

        return $this->saveEstimatedCost($da, $valueElement->innertext());

    }

    protected function extractOfficer($html, $da, $params = null): bool {

        $officerLabelElement = $html->find("label[for=Officer]", 0);
        if ($officerLabelElement === null) {
            return false;
        }

        // <b> containing the <label>
        $officerParentElement = $officerLabelElement->parent();
        if ($officerParentElement === null) {
            return false;
        }

        // Get <a> next to <b>
        $valueElement = $officerParentElement->next_sibling();
        if ($valueElement === null || $valueElement->tag !== "a") {
            return false;
        }

        $role = "Officer";
        $name = $this->cleanString($valueElement->innertext());

        return $this->saveParty($da, $role, $name);

    }

    protected function extractDocuments($html, $da, $params = null): bool {
        $addedDocuments = 0;
        $documentsElement = $html->find("div[id=documents_info]", 0);

        if ($documentsElement === null) {
            return false;
        }

        $ulElement = $documentsElement->children(0);
        if ($ulElement === null) {
            return false;
        }

        $documentListElements = $ulElement->children();
        foreach ($documentListElements as $documentListElement) {

            $anchorElement = $documentListElement->children(0);
            if ($anchorElement === null) {
                continue;
            }

            $documentUrl = $this->cleanString($anchorElement->href);
            $documentName = $this->cleanString($anchorElement->innertext());

            if ($this->saveDocument($da, $documentName, $documentUrl)) {
                $addedDocuments++;
            }
        }

        return ($addedDocuments > 0);
    }
}