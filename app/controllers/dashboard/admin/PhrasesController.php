<?php

namespace Aiden\Controllers\Admin;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Phrases;
use Aiden\Forms\CreatePhraseForm;

class PhrasesController extends _BaseController {

    public function indexAction() {

        $this->view->setVars([
            'page_title' => 'Phrases',
        ]);

        $this->view->pick('admin/phrases/index');

    }

}
