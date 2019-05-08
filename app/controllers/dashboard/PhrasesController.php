<?php

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Das;
use Aiden\Models\DasUsers;
use Aiden\Models\UsersPhrases;
use Aiden\Models\Councils;
use Aiden\Forms\CreatePhraseForm;
use Phalcon\Mvc\Model\Manager;

class PhrasesController extends _BaseController {

    public function indexAction() {
        $councils = Councils::find([
            'conditions' => '1=1 ORDER BY name ASC'
        ]);
        $dasMaxCost = Das::find([
            'conditions' => '1=1 ORDER BY estimated_cost DESC LIMIT 1'
        ]);
        $councilIds = array();
        foreach($councils as $row){
            $councilIds[] = $row->getId();
        }

        $this->view->setVars([
            "page_title" => 'Filters',
            "councils" => $councils,
            "councilIds" => json_encode($councilIds),
            "maxCost" => 50000000,
            "maxCostValue" => $dasMaxCost[0]->getEstimatedCost()
        ]);
    }

    public function createAction() {

        // Validate the form
        $createPhraseForm = new CreatePhraseForm();
//        if (!$createPhraseForm->isValid($this->request->getPost())) {
//            foreach ($createPhraseForm->getMessages() as $errorMessage) {
//                $this->flashSession->error($errorMessage);
//            }
//            return $this->response->redirect('phrases');
//        }

        $inputPhrase = ($this->request->getPost('inputPhrase') != '' ? $this->request->getPost('inputPhrase') : '-');
        $inputCaseSensitive = $this->request->getPost('inputCaseSensitive');
        $inputLiteralSearch = $this->request->getPost('inputLiteralSearch');
        $inputExcludePhrase = $this->request->getPost('inputExcludePhrase');
        $inputMetadata = $this->request->getPost('inputMetadata');
        $filter = $this->request->getPost('filter');
        $councils = $this->request->getPost('councils');
        $costFrom = $this->request->getPost('costFrom');
        $costTo = $this->request->getPost('costTo');

        if($inputPhrase !== '-'){
            $phrase = UsersPhrases::findFirst([
                'conditions' => 'phrase = :phrase: AND users_id = :users_id:',
                'bind' => [
                    "phrase" => $inputPhrase,
                    "users_id" => $this->getUser()->getId()
                ]
            ]);
        }else{
            $phrase = false;
        }

        // Create Phrase (if required)
        if ($phrase === false) {

            $phrase = new UsersPhrases();
            $phrase->setUserId($this->getUser()->getId());
            $phrase->setPhrase($inputPhrase);
            $phrase->setCaseSensitive($inputCaseSensitive);
            $phrase->setLiteralSearch($inputLiteralSearch);
            $phrase->setExcludePhrase($inputExcludePhrase);
            $phrase->setMetadata($inputMetadata);
            $phrase->setFilterBy(($filter == 'all' ? 'all' : json_encode($filter)));
            $phrase->setCouncils(($councils == 'all' ? 'all' : json_encode($councils)));
            $phrase->setCostFrom($costFrom);
            $phrase->setCostTo($costTo);
            $phrase->setCreated(new \DateTime());

            if ($phrase->save()) {
                // TODO: Logic
                return json_encode(true);
            }
            else {
                foreach ($phrase->getMessages() as $errorMessage) {
                    $this->flashSession->error($errorMessage);
                }
                return json_encode(false);
            }
        }
        else {

            $message = "The phrase already exists";
            $this->flashSession->error($message);
            return json_encode(false);
        }

    }

    public function flipCaseAction() {

        // Attempt to find the lead user wants to save
        $usersPhrases = UsersPhrases::findFirst([
                    'conditions' => 'id = :id: AND users_id = :users_id:',
                    'bind' => [
                        'id' => $this->request->getPost('phrase_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        $response = "";

        if ($usersPhrases === false) {

            $response = [
                "status" => "error",
                "message" => "The phrase could not be found."
            ];
        }

        // Relation exists, update status
        else {

            $usersPhrases->setCaseSensitive(!$usersPhrases->getCaseSensitive());
            if ($usersPhrases->save()) {

                $response = [
                    "status" => "ok",
                    "message" => "Updated case sensitivity to " . $usersPhrases->getCaseSensitive(),
                    "phrase_id" => $usersPhrases->getId()
                ];
            }
            else {

                $response = [
                    "status" => "error",
                    "message" => $usersPhrases->getMessages(),
                ];
            }
        }

        echo json_encode($response);
        $this->view->disable();

    }

    public function flipLiteralAction() {

        // Attempt to find the lead user wants to save
        $usersPhrases = UsersPhrases::findFirst([
                    'conditions' => 'id = :id: AND users_id = :users_id:',
                    'bind' => [
                        'id' => $this->request->getPost('phrase_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        $response = "";

        if ($usersPhrases === false) {

            $response = [
                "status" => "error",
                "message" => "The phrase could not be found."
            ];
        }

        // Relation exists, update status
        else {

            $du = DasUsers::find([
                'conditions' => 'users_id = :users_id: AND users_phrase_id = :users_phrase_id:',
                'bind' => [
                    "users_id" => $this->getUser()->getId(),
                    "users_phrase_id" => $this->request->getPost('phrase_id')
                ]
            ]);
            if($du){
                $du->delete();
            }

            $usersPhrases->setLiteralSearch(!$usersPhrases->getLiteralSearch());
            if ($usersPhrases->save()) {

                $response = [
                    "status" => "ok",
                    "message" => "Updated case sensitivity to " . $usersPhrases->getLiteralSearch(),
                    "phrase_id" => $usersPhrases->getId()
                ];
            }
            else {

                $response = [
                    "status" => "error",
                    "message" => $usersPhrases->getMessages(),
                ];
            }



        }

        echo json_encode($response);
        $this->view->disable();

    }

    public function flipExcludeAction() {

        // Attempt to find the lead user wants to save
        $usersPhrases = UsersPhrases::findFirst([
                    'conditions' => 'id = :id: AND users_id = :users_id:',
                    'bind' => [
                        'id' => $this->request->getPost('phrase_id'),
                        'users_id' => $this->getUser()->getId()
                    ]
        ]);

        $response = "";

        if ($usersPhrases === false) {

            $response = [
                "status" => "error",
                "message" => "The phrase could not be found."
            ];
        }

        // Relation exists, update status
        else {

            $usersPhrases->setExcludePhrase(!$usersPhrases->getExcludePhrase());
            if ($usersPhrases->save()) {

                $response = [
                    "status" => "ok",
                    "message" => "Updated case sensitivity to " . $usersPhrases->getExcludePhrase(),
                    "phrase_id" => $usersPhrases->getId()
                ];
            }
            else {

                $response = [
                    "status" => "error",
                    "message" => $usersPhrases->getMessages(),
                ];
            }
        }

        echo json_encode($response);
        $this->view->disable();

    }

    public function deleteAction() {

        $deletedIds = [];

        $ids = $this->request->getPost("ids");
        foreach ($ids as $id) {
            $usersPhrases = UsersPhrases::findFirst([
                        'conditions' => 'id = :id: AND users_id = :users_id:',
                        'bind' => [
                            'id' => $id,
                            'users_id' => $this->getUser()->getId()
                        ]
            ]);

            if ($usersPhrases) {
                 if ($usersPhrases->delete()) {
                    $deletedIds[] = $id;
                    // delete clear users phrases and rewrite aftersave
                    $du = DasUsers::find([
                        'conditions' => 'users_id = :users_id: AND users_phrase_id = :users_phrase_id:',
                        'bind' => [
                            "users_id" => $this->getUser()->getId(),
                            "users_phrase_id" => $id
                        ]
                    ]);
                    if($du){
                        $du->delete();
                    }
                 }
            }
        }

        $response = [
            "status" => "ok",
            "phrase_ids" => $deletedIds,
        ];

        echo json_encode($response);
        $this->view->disable();

    }



    public function getAction(){
        $phrase = UsersPhrases::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->request->getPost("id")
            ]
        ]);

        if($phrase){

            return json_encode(array(
               'id' => $phrase->getId(),
                'phrase' => $phrase->getPhrase(),
                'caseSensitive' => $phrase->getCaseSensitive(),
                'literalSearch' => $phrase->getLiteralSearch(),
                'excludePhrase' => $phrase->getExcludePhrase(),
                'metadata' => $phrase->getMetadata(),
                'filterBy' => $phrase->getFilterBy(),
                'councils' => $phrase->getCouncils(),
                'costFrom' => $phrase->getCostFrom(),
                'costTo' => $phrase->getCostTo()
            ));

        }else{
            return json_encode(false);
        }

    }

}
