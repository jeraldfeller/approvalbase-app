<?php

namespace Common\Controllers;

class IndexController extends _BaseController {

    public function indexAction() {

        $this->view->setVars([
            'form' => new \Aiden\Forms\RegisterForm(),
            'page_title' => 'ApprovalBase',
            'user'  => $this->getUser()
        ]);
        return $this->response->redirect('dashboard', false, 302);
    }

}
