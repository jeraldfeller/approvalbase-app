<?php

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Councils;
use Aiden\Models\UsersCouncils;

class CouncilsController extends _BaseController {

    public function indexAction() {

        $this->view->setVars([
            'page_title' => 'Councils',
        ]);

        $this->view->pick("councils/index");

    }

    public function subscribeAction() {

        // Council we're about to unsubscribe from
        $council = Councils::findFirst([
                    "conditions" => "id = :councils_id:",
                    "bind" => [
                        "councils_id" => $this->dispatcher->getParam("council_id")
                    ]
        ]);

        if ($council === false) {

            $this->flashSession->error("The specified council does not exist");
            return $this->response->redirect('councils');
        }

        if (!$usersCouncils) {

            $usersCouncils = new UsersCouncils();
            $usersCouncils->setUserId($this->getUser()->getId());
            $usersCouncils->setCouncilId($council->getId());

            if ($usersCouncils->save()) {
                $this->flashSession->success("You are now subscribed to " . $council->getName());
            }
            else {
                $this->flashSession->error("There was an error subscribing to " . $council->getName());
            }
        }
        else {
            $this->flashSession->notice("You were already subscribed to " . $council->getName());
        }

        return $this->response->redirect('councils');

    }

    public function unsubscribeAction() {

        // Council we're about to unsubscribe from
        $council = Councils::findFirst([
                    "conditions" => "id = :councils_id:",
                    "bind" => [
                        "councils_id" => $this->dispatcher->getParam("council_id")
                    ]
        ]);

        if ($council === false) {

            $this->flashSession->error("The specified council does not exist");
            return $this->response->redirect('councils');
        }

        // Check if there's a relation between User and Council
        $usersCouncils = UsersCouncils::findFirst([
                    "conditions" => "users_id = :users_id: AND councils_id = :councils_id:",
                    "bind" => [
                        "users_id" => $this->getUser()->getId(),
                        "councils_id" => $council->getId()
                    ]
        ]);

        if ($usersCouncils !== false) {

            if ($usersCouncils->delete()) {
                $this->flashSession->success("You are no longer subscribed to " . $council->getName());
            }
            else {
                $this->flashSession->error("There was an error unsubscribing from " . $council->getName());
            }
        }
        else {
            $this->flashSession->notice("You weren't subscribed to " . $council->getName());
        }

        return $this->response->redirect('councils');

    }

    public function bulkSubscribeAction() {

        $council_ids = $this->request->getPost('council_ids');
        $savedIds = [];

        // Loop through values and attempt to update their statuses
        foreach ($council_ids as $council_id) {

            $council_id = intval($council_id);
            if (!$council_id) {
                continue;
            }

            $usersCouncils = UsersCouncils::findFirst([
                        'conditions' => 'users_id = :users_id: AND councils_id = :councils_id:',
                        'bind' => [
                            'users_id' => $this->getUser()->getId(),
                            'councils_id' => $council_id
                        ]
            ]);

            if ($usersCouncils) {

                // If relation exists, the user is already subscribed, pretend we updated it?
                $savedIds[] = $council_id;
            }
            else {

                $usersCouncils = new UsersCouncils();
                $usersCouncils->setUserId($this->getUser()->getId());
                $usersCouncils->setCouncilId($council_id);

                if ($usersCouncils->save()) {
                    $savedIds[] = $council_id;
                }
                else {
                    $this->logger->error("Could not create relation between User {user_id} and Council {council_id} ({error})", [
                        "user_id" => $this->getUser()->getId(),
                        "council_id" => $council_id,
                        "error" => print_r($usersCouncils->getMessages(), true)
                    ]);
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

    public function bulkUnsubscribeAction() {

        $council_ids = $this->request->getPost('council_ids');
        $savedIds = [];

        // Loop through values and attempt to update their statuses
        foreach ($council_ids as $council_id) {

            $council_id = intval($council_id);
            if (!$council_id) {
                continue;
            }

            $usersCouncils = UsersCouncils::findFirst([
                        'conditions' => 'users_id = :users_id: AND councils_id = :councils_id:',
                        'bind' => [
                            'users_id' => $this->getUser()->getId(),
                            'councils_id' => $council_id
                        ]
            ]);

            if ($usersCouncils) {

                if ($usersCouncils->delete()) {
                    $savedIds[] = $council_id;
                }
                else {
                    $this->logger->error("Could not delete relation between User {user_id} and Council {council_id} ({error})", [
                        "user_id" => $this->getUser()->getId(),
                        "council_id" => $council_id,
                        "error" => print_r($usersCouncils->getMessages(), true)
                    ]);
                }
            }
            else {
                // User wasn't subscribed            
                $savedIds[] = $council_id;
            }
        }

        $response = [
            'status' => 'OK',
            'saved_ids' => $savedIds
        ];

        echo json_encode($response);
        $this->view->disable();

    }




}
