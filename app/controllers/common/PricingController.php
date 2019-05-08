<?php

namespace Common\Controllers;

class PricingController extends _BaseController {

    public function indexAction() {
        $this->view->setVars([
            'user'  => $this->getUser(),
            'page_title' => 'Pricing | Approval Base'
        ]);

    }

}
