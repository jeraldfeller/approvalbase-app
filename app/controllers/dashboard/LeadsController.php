<?php

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Das;
use Aiden\Models\DasUsers;
use Aiden\Models\Councils;
use Aiden\Models\DasUsersSearch;

class LeadsController extends _BaseController {

    public function indexAction() {

        if($this->getUser()->level == 1){
            if($this->getUser()->solution != 'search'){
                return json_encode('error');
            }
        }
        $leadId = ($this->request->getQuery("l") ? $this->request->getQuery("l") : '');
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

        $onboardingStatus =  ($this->request->getQuery("t") ? 1 : 0);
        $this->view->setVars([
            'page_title' => 'Leads',
            'lead_status' => DasUsers::STATUS_LEAD,
            'currentViewedLead' => $leadId,
            "defaultDateRange" => array(date('m/d/Y', strtotime('-6 months')), date('m/d/Y', strtotime('+ 1 days'))),
            "maxCost" => 50000000,
            "maxCostValue" => $dasMaxCost[0]->getEstimatedCost(),
            "councils" => $councils,
            "onboardingStatus" => $onboardingStatus
        ]);

        $this->view->pick('leads/index');

    }

    public function indexSavedAction() {
        if($this->getUser()->level == 1){
            if($this->getUser()->solution != 'search'){
                return json_encode('error');
            }
        }
        $dasMaxCost = Das::find([
            'conditions' => '1=1 ORDER BY estimated_cost DESC LIMIT 1'
        ]);
        $councils = Councils::find([
            'conditions' => '1=1 ORDER BY name ASC'
        ]);

        $leadId = ($this->request->getQuery("l") ? $this->request->getQuery("l") : '');
        $this->view->setVars([
            'currentViewedLead' => $leadId,
            'page_title' => 'Saved Leads',
            'lead_status' => DasUsers::STATUS_SAVED,
            "defaultDateRange" => array(date('m/d/Y', strtotime('-1 year')), date('m/d/Y', strtotime('+ 1 days'))),
            "maxCost" => 100000000,
            "maxCostValue" => $dasMaxCost[0]->getEstimatedCost(),
            "councils" => $councils,
        ]);

        $this->view->pick('leads/index');

    }

    public function viewAction() {
       $da = Das::findFirstById($this->dispatcher->getParam('lead_id'));
       if (!$da) {
           $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show404']);
       }

       // Mark as read/seen
       $daUsers = $da->getLinkedDasUsers($this->getUser());
       if ($daUsers !== null) {
           $daUsers->setSeen(1);
           $daUsers->save();
       }


       $this->view->setVars([
           'da' => $da,
           'googleMapAPI' => $this->config->googleMapAPI,
           'leadId' => $this->dispatcher->getParam('lead_id'),
           'isAdmin' => ($this->request->getQuery("a") ? $this->request->getQuery("a") : 0),
           'from' => ($this->request->getQuery("from") ? $this->request->getQuery("from") : 'leads'),
       ]);

   }

    public function restoreAction() {

        // Attempt to find the lead user wants to save
        $daUsers = DasUsers::findFirst([
                    'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                    'bind' => [
                        'das_id' => $this->dispatcher->getParam('lead_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        // Non-existant lead
        if (!$daUsers) {

            $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show404']);
            return false;
        }

        // Update the status
        $daUsers->setStatus(DasUsers::STATUS_LEAD);

        // Save the model
        if ($daUsers->save()) {

            $message = 'The Development Application has been restored. '
                    . '<a href="' . $daUsers->Da->getViewUrl(false) . '">Click here to view</a>';
            $this->flashSession->success($message);

            return $this->response->redirect('leads');
        }
        else {

            foreach ($daUsers->getMessages() as $errorMessage) {

                $this->flashSession->error($errorMessage);
            }
            return $this->response->redirect('leads');
        }

    }

    public function saveAction() {

        // Forward if AJAX request
        if ($this->request->getQuery("ajax") == 1) {
            return $this->dispatcher->forward(["action" => "ajaxSave"]);
        }

        // Attempt to find the lead user wants to save
        $daUsers = DasUsers::findFirst([
                    'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                    'bind' => [
                        'das_id' => $this->dispatcher->getPost('lead_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        // Non-existant relation between lead and user
        if (!$daUsers) {

            $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show404']);
            return false;
        }

        // Update the status
        $daUsers->setStatus(DasUsers::STATUS_SAVED);
        // Save the model
        if ($daUsers->save()) {

            $message = 'The Development Application has been saved. '
                    . '<a href="' . $daUsers->Da->getViewUrl(false) . '">Click here to view</a>';
            $this->flashSession->success($message);

            return $this->response->redirect('leads');
        }
        else {

            foreach ($daUsers->getMessages() as $errorMessage) {

                $this->flashSession->error($errorMessage);
            }
            return $this->response->redirect('leads');
        }

    }

    public function ajaxSaveAction() {

        // Attempt to find the lead user wants to save
        $daUsers = DasUsers::findFirst([
                    'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                    'bind' => [
                        'das_id' => $this->request->getPost('lead_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        $response = "";

        // Non-existant relation between lead and user
        if (!$daUsers) {

            $response = [
                "status" => "error",
                "message" => "The development application could not be found."
            ];
        }

        // Relation exists, update status
        else {
            $totalSaved = 0;

            // Flip status allows for single function
            if ($daUsers->getStatus() === DasUsers::STATUS_LEAD) {
                $daUsers->setStatus(DasUsers::STATUS_SAVED);
                $daUsers->setLastUpdate(new \DateTime());

                DasUsersSearch::updateSavedSearch($this->getUser()->getId(), $this->request->getPost('lead_id'), DasUsers::STATUS_SAVED);
            }
            else {
                $daUsers->setStatus(DasUsers::STATUS_LEAD);
                DasUsersSearch::updateSavedSearch($this->getUser()->getId(), $this->request->getPost('lead_id'), DasUsers::STATUS_LEAD);
            }

            if ($daUsers->save()) {

                // Get total saved for display
                $totalSaved = DasUsers::find([
                            'conditions' => 'users_id = :users_id: AND status = :status:',
                            'bind' => [
                                'users_id' => $this->getUser()->getId(),
                                'status' => DasUsers::STATUS_SAVED
                            ]
                        ])->count();

                $response = [
                    "status" => "ok",
                    "message" => "Updated status to " . $daUsers->getStatus(),
                    "lead_id" => $daUsers->Da->getId(),
                    "totalSaved" => $totalSaved
                ];
            }
            else {

                $response = [
                    "status" => "error",
                    "message" => $daUsers->getMessages(),
                    "totalSaved" => $totalSaved
                ];
            }
        }

        echo json_encode($response);
        $this->view->disable();

    }

    public function bulkMarkAsReadAction() {

        $lead_ids = $this->request->getPost('lead_ids');
        $savedIds = [];

        // Loop through values and attempt to update their statuses
        foreach ($lead_ids as $leadId) {

            $leadId = intval($leadId);
            if (!$leadId) {
                continue;
            }

            $developmentApplicationUser = DasUsers::findFirst([
                        'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                        'bind' => [
                            'das_id' => $leadId,
                            'users_id' => $this->getUser()->getId()
                        ]
            ]);

            // Only update when lead wasn't saved before
            if ($developmentApplicationUser) {

                // Update Status
                $developmentApplicationUser->setSeen(1);

                // Add to $savedIds array so we can inform client
                if ($developmentApplicationUser->save()) {

                    $savedIds[] = $leadId;
                }
            }
        }

        $response = [
            'status' => 'OK',
            'saved_ids' => $savedIds
        ];

        echo json_encode($response);
        $this->view->disable();

    }

    public function bulkMarkAsUnreadAction() {

        $lead_ids = $this->request->getPost('lead_ids');
        $savedIds = [];

// Loop through values and attempt to update their statuses
        foreach ($lead_ids as $leadId) {

            $leadId = intval($leadId);
            if (!$leadId) {
                continue;
            }

            $developmentApplicationUser = DasUsers::findFirst([
                        'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                        'bind' => [
                            'das_id' => $leadId,
                            'users_id' => $this->getUser()->getId()
                        ]
            ]);

// Only update when lead wasn't saved before
            if ($developmentApplicationUser) {

// Update Status
                $developmentApplicationUser->setSeen(0);

// Add to $savedIds array so we can inform client
                if ($developmentApplicationUser->save()) {

                    $savedIds[] = $leadId;
                }

                //die (print_r($developmentApplicationUser->toArray()));
            }
        }

        $response = [
            'status' => 'OK',
            'saved_ids' => $savedIds
        ];

        echo json_encode($response);
        $this->view->disable();

    }


    public function bulkUpdateLeadStatusAction() {
        $lead_ids = $this->request->getPost('lead_ids');
        for($x = 0; $x < count($lead_ids); $x++){
            $leadId = $lead_ids[$x];
            $entity = DasUsers::findFirst([
                'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                'bind' => [
                    'das_id' => $leadId,
                    'users_id' => $this->getUser()->getId()
                ]
            ]);
            if($entity){
                $entity->setStatus(1);
                $entity->setSeen(0);
                $entity->save();
            }
        }

        echo json_encode(array('status' => 'OK'));
        $this->view->disable();

    }

    public function bulkExportCsvAction() {

        $lead_ids = $this->request->getPost('lead_ids');

        if (!is_array($lead_ids)) {
            return false;
        }

        // Loop through values
        $das = [];
        foreach ($lead_ids as $leadId) {

            $leadId = intval($leadId);
            if (!$leadId) {
                continue;
            }

            $developmentApplicationUser = DasUsers::findFirst([
                        'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
                        'bind' => [
                            'das_id' => $leadId,
                            'users_id' => $this->getUser()->getId()
                        ]
            ]);

            // Only update when lead wasn't saved before
            if ($developmentApplicationUser) {
                $das[] = $developmentApplicationUser->Da;
            }
        }

        $csvContent = "Council,Reference,URL,Address,Description,Lodged,Estimated Cost,Applicant,First Name,Last Name,LinkedIn,Phone,Email\r\n";

        foreach ($das as $da) {

            $csvContent .= '"' . $da->Council->getName() . "\",";
            $csvContent .= '"' . $da->getCouncilReference() . "\",";
            $csvContent .= '"' . $da->getCouncilUrl() . "\",";
            $csvContent .= '"' . $da->getAddress() . "\",";
            $csvContent .= '"' . $da->getDescription() . "\",";
            $csvContent .= '"' . $da->getLodgeDate()->format('d-m-Y') . "\",";
            $csvContent .= '"' . $da->getEstimatedCost() . "\",";
            $csvContent .= '"' . $da->getApplicant() . "\",";
            $csvContent .= '"' . $da->getFirstName() . "\",";
            $csvContent .= '"' . $da->getLastName() . "\",";
            $csvContent .= '"' . $da->getLinkedInUrl() . "\",";
            $csvContent .= '"' . $da->getPhone() . "\",";
            $csvContent .= '"' . $da->getEmail() . "\",";
            $csvContent .= "\r\n";
        }

        $fileName = "leads-" . md5($csvContent) . ".csv";

        $this->response->setHeader("Content-Type", "application/octet-stream");
        $this->response->setHeader("Content-Disposition", "attachment; filename=\"" . $fileName . "\"");

        echo $csvContent;

        $this->view->disable();

    }


    public function getNewAlertCountAction(){

        $result = DasUsers::find([
            'conditions' => 'users_id = :usersId: AND seen = :seen: AND show_on_alerts = 1',
            'bind' => [
                'usersId' => $this->getUser()->getId(),
                'seen' => 0
            ]
        ]);

        if($this->request->getPost('ajax')){
            echo count($result);
        }else{
            return count($result);
        }

    }
}
