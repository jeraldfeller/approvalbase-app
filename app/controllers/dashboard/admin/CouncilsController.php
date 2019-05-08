<?php

namespace Aiden\Controllers\Admin;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Councils;

class CouncilsController extends _BaseController {

    public function indexAction() {

        $this->view->setVars([
            'page_title' => 'Councils'
        ]);

        $this->view->pick("admin/councils/index");

    }

}
