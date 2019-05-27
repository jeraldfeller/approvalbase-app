<?php

namespace Common\Controllers;

use Aiden\Models\Users;

class _BaseController extends \Phalcon\Mvc\Controller {

    private $user;

    public function initialize() {


        if ($this->session->has('auth')) {
            $this->user = $this->session->get('auth')['user'];
            $this->view->setVar("loggedInUser", $this->getUser());
        }

    }

    public function afterExecuteRoute() {
        $this->view->setViewsDir($this->getDI()->get('config')->directories->viewsDirCommon);
    }

    public function getUser() {
        return $this->user;
    }

    public function generateRandomString($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return time().$randomString;
    }

}
