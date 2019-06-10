<?php

namespace Aiden\Controllers\Admin;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Users;

class UsersController extends _BaseController {

    public function indexAction() {

        $this->view->pick('admin/users/index');

    }

    public function viewAction() {

        $userId = $this->dispatcher->getParam("user_id");
        $user = Users::findFirstById($userId);
        if (!$user) {
            $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show404']);
        }

        $this->view->setVar("user", $user);

        $this->view->pick('admin/users/view');

    }

    public function updateAction() {

        $userId = $this->dispatcher->getParam("user_id");
        $user = Users::findFirstById($userId);
        if (!$user) {
            $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show404']);
        }

        if ($user->getLevel() === Users::LEVEL_ADMINISTRATOR) {
            $this->dispatcher->forward(['controller' => 'errors', 'action' => 'show401']);
        }

        $level = $this->request->getPost("input_level");

        switch ($level) {
            case Users::LEVEL_ADMINISTRATOR:
                $user->setLevel(Users::LEVEL_ADMINISTRATOR);
                break;
            case Users::LEVEL_USER:
                $user->setLevel(Users::LEVEL_USER);
                break;
            default:
                $user->setLevel($user->getLevel());
                break;
        }

        if ($user->save()) {
            // TODO: Log
        }
        else {
            // TODO: Log
        }

        $this->response->redirect("admin/users");

    }

    public function reactivateFreeTrialAction(){
        $id = $this->request->getPost('id');
        // get user with expired status only
        $user = Users::findFirst([
            'conditions' => 'id = :id: AND subscription_status = :status:',
            'bind' => [
                'id' => $id,
                'status' => 'expired'
            ]
        ]);
        if($user){
            $date = new \DateTime();
            $user->setCreated($date);
            $user->setSubscriptionStatus('trial');
            $user->save();

            $email = $user->getEmail();
            $user = null;
            return json_encode([
                'message' => $email. ' free trial period has been reactivated.',
                'success' => true
            ]);
        }else{
            $user = null;
            return json_encode([
               'message' => 'User is subscription is not expired.',
                'success' => false
            ]);
        }

    }

    public function sendEmailAction(){
        $id = $this->request->getPost('id');
        $user = Users::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);

        if($user){
            $email = $user->getEmail();
            $name = $user->getName();
            $lname = $user->getLastName();
            $sendMail =  \Aiden\Models\Email::welcomeNotification($email, $name, $lname);
            if($sendMail){
                $user = null;
                return json_encode([
                    'success' => true,
                    'message' => 'Welcome email successfully sent.'
                ]);
            }else{
                $user = null;
                return json_encode([
                    'success' => false,
                    'message' => ''
                ]);
            }
        }else{
            $user = null;
            return json_encode([
               'success' => false,
               'message' => ''
            ]);
        }

    }

    public function deleteUserAction(){
        $id = $this->request->getPost('id');
        $user = Users::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);

        if($user){
            $email = $email = $user->getEmail();
            $user->delete();
            $return = json_encode([
                'success' => true,
                'message' => $email . ' successsfully deleted.'
            ]);
        }else{
            $return = json_encode([
                'success' => false,
                'message' => ''
            ]);
        }
        $user = null;

        return $return;
    }

}
