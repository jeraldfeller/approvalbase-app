<?php

namespace Aiden\Controllers\Admin;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Das;
use Aiden\Models\Councils;
use Aiden\Forms\EditLeadForm;

class LeadsController extends _BaseController {

    public function indexAction() {

        $this->view->setVars([
            'page_title' => 'Leads',
            'lead_status' => Das::STATUS_LEAD,
            'councils' => Councils::find(),
        ]);

        $this->view->pick("admin/leads/index");

    }

}
