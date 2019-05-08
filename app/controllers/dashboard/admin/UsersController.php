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

}
