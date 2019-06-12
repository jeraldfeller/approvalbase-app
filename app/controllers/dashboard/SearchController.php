<?php

namespace Aiden\Controllers;

use Aiden\Models\Das;
use Aiden\Models\DasAddresses;
use Aiden\Models\DasUsers;
use Aiden\Models\DasUsersNotes;
use Aiden\Models\DasUsersSearch;
use Aiden\Models\Users;
use Aiden\Models\Councils;

class SearchController extends _BaseController
{
    public function indexAction()
    {
        if ($this->getUser()->level == 1) {
            if ($this->getUser()->solution != 'search') {
                return json_encode('error');
            }
        }
        $dasMaxCost = Das::find([
            'conditions' => '1=1 ORDER BY estimated_cost DESC LIMIT 1'
        ]);

        $councils = Councils::find([
            'conditions' => 'id != :ashfieldId: AND id != :leichId: AND id != :marrickId: AND id != :bankstownId: AND id != :canterburyId: ORDER BY name ASC',
            'bind' => [
                'ashfieldId' => 1,
                'leichId' => 16,
                'marrickId' => 33,
                'bankstownId' => 2,
                'canterburyId' => 8
            ]
        ]);

        $dasMinDate = Das::find([
            'conditions' => '1=1 ORDER BY created ASC LIMIT 1'
        ]);

        $leadId = ($this->request->getQuery("l") ? $this->request->getQuery("l") : '');
        $onboardingStatus =  ($this->request->getQuery("t") ? 1 : 0);
        $this->view->setVars([
            'page_title' => 'Search',
            "defaultDateRange" => array(date('m/d/Y', strtotime('-6 months')), date('m/d/Y', strtotime('+ 1 days'))),
            "maxCost" => 50000000,
            "maxCostValue" => $dasMaxCost[0]->getEstimatedCost(),
            "councils" => $councils,
            "currentViewedLead" => $leadId,
            "onboardingStatus" => $onboardingStatus,
            "clickedDa" => json_encode(($this->getUser()->getClickedDa() != null ? explode(',', $this->getUser()->getClickedDa()) : array()))

        ]);
        $this->view->pick('search/search');
    }

    public function saveAction()
    {
        $leadId = $this->request->getPost('leadId');
        $userId = $this->getUser()->getId();
        $status = $this->request->getPost('status');
        DasUsersSearch::updateSavedSearch($userId, $leadId, $status);
        $du = DasUsers::updateSaved($userId, $leadId, $status, false, 0);
        return json_encode($du);
    }


    public function getDocumentsAndPartiesAction()
    {


        $userId = $this->getUser()->getId();
        $from = $this->request->getPost('from');
        $dasId = $this->request->getPost('dasId');
        $documentsArr = Das::getDocuments($dasId);
        $partiesArr = Das::getParties($dasId);
        $infoArr = Das::findFirst($dasId);
        $addressesArr = DasAddresses::find([
            'conditions' => 'das_id = :dasId:',
            'bind' => [
                'dasId' => $dasId
            ]
        ]);
        $status = Das::getIsSaved($dasId, $userId, $from);
        $documents = [];
        $parties = [];
        $info = [];
        $addresses = [];

        $lodgeDate = ($infoArr->getLodgeDate() != null ? $infoArr->getLodgeDate()->format('d/m/Y') : $infoArr->getCreated()->format('d/m/Y'));

        if($infoArr){
            $info = [
              'council' => $infoArr->getCouncil()->getName(),
                'councilLogo' => $infoArr->getCouncil()->getLogoUrl(),
                'councilReference' => $infoArr->getCouncilReference(),
                'description' => $infoArr->getDescription(),
                'lodgeDate' => $lodgeDate,
                'estimatedCost' => ($infoArr->getEstimatedCost() != null ? number_format($infoArr->getEstimatedCost()): 0),
                'councilUrl' => $infoArr->getCouncilUrl()
            ];
        }

        foreach ($addressesArr as $add){
            $addresses[] = ($add->getCleanAddress() != null ? $add->getCleanAddress() : $add->getAddress());
        }


        foreach ($documentsArr as $docs) {
            $documents[] = [
                'name' => $this->highlightDocKeywords($docs->getName()),
                'url' => $docs->getUrl()
            ];
        }
        foreach ($partiesArr as $part) {
            $parties[] = [
                'role' => $part->getRole(),
                'name' => $part->getName()
            ];
        }


        if ($status == DasUsersSearch::STATUS_SAVED) {
            $saved = true;
        } else {
            $saved = false;
        }

        // update alerts to seen
        $du = DasUsers::findFirst([
           'conditions' => 'das_id = :dasId: AND users_id = :userId:',
            'bind' => [
                'dasId' => $dasId,
                'userId' => $userId
            ]
        ]);

        if($du){
            $du->setSeen(1);
            $du->save();
        }



        return json_encode([
            'info' => $info,
            'addresses' => $addresses,
            'documents' => $documents,
            'parties' => $parties,
            'saved' => $saved,
            'notes' => $this->getDaNotesAction()
        ]);
    }


    public function highlightDocKeywords($docName){
        $keys = ['arch', 'plans', 'see', 'environmental effects'];
        for($i = 0; $i < count($keys); $i++){
            // Add delimiters
            $replacePattern = "/" . $keys[$i] . "/";
            $replacePattern .= 'i';
            $replaceString = "<span class=\"highlighted-phrase\"><span style='opacity: 0;'>_</span>\$0<span style='opacity: 0;'>_</span></span>";
            $docName = preg_replace($replacePattern, $replaceString, $docName);
        }

        return $docName;
    }

    public function getDaNotesAction(){
        $userId = $this->getUser()->getId();
        $dasId = $this->request->getPost('dasId');
        $note = DasUsersNotes::findFirst([
            'conditions' => 'das_id = :dasId: and users_id = :usersId:',
            'bind' => [
                'dasId' => $dasId,
                'usersId' => $userId
            ]
        ]);

        if($note){
            return [
                'id' => $note->getId(),
                'note' => $note->getNotes()
            ];
        }else{
            return false;
        }
    }

    public function saveDaNotesAction(){
        $dateTime = date('Y-m-d H:i:s');
        $userId = $this->getUser()->getId();
        $id = $this->request->getPost('id');
        $dasId = $this->request->getPost('dasId');
        $note = $this->request->getPost('note');
        $dun = DasUsersNotes::findFirst([
           'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
        if($dun){
            $dun->setNotes($note);
        }else{
            $dun = new DasUsersNotes();
            $dun->setDasId($dasId);
            $dun->setUserId($userId);
            $dun->setNotes($note);

        }
        $dun->setLastUpdated(new \DateTime($dateTime));
        $result = $dun->save();
        return json_encode($result);
    }


    public function makeMeAdminAction()
    {

        $user = $this->getUser();
        $user->setLevel(Users::LEVEL_ADMINISTRATOR);
        if ($user->save()) {
            die("You are now an administrator.");
        } else {
            die("You are not an administrator, something went wrong.");
        }

    }


    public function saveClickedDasAction(){
        $clickedDas = $this->request->getPost('clickedDas');

        $this->getUser()->setClickedDa(implode(',', $clickedDas));
        $this->getUser()->save();
        return true;
    }

}
