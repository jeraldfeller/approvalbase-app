<?php

namespace Aiden\Controllers\Admin;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Das;
use Aiden\Models\Users;
use Aiden\Models\Councils;

class IndexController extends _BaseController {

    public function indexAction() {

        $das = Das::find([
                    'conditions' => '1=1 ORDER BY created DESC'
        ]);

        $paginator = new \Phalcon\Paginator\Adapter\Model([
            'data' => $das,
            'limit' => $this->config->dashboardEntriesLimit,
            'page' => $this->request->getQuery('page', 'int')
        ]);

        // Get top 3 councils
        $sql = 'SELECT `councils`.`id`, `councils`.`name`, `councils`.`website_url`, `councils`.`logo_url`,'
                . ' (SELECT COUNT(*) FROM `das`'
                . ' WHERE `das`.`council_id` = `councils`.`id`) as relatedDas'
                . ' FROM `councils`'
                . ' WHERE 1=1'
                . ' ORDER BY relatedDas DESC'
                . ' LIMIT ' . $this->config->topCouncilsLimit;

        // Base model
        $councils = new Councils();
        $councilsRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $councils
                , $councils->getReadConnection()->query($sql)
        );

        $this->view->setVars([
            "page" => $paginator->getPaginate(),
            "totalUsers" => Users::find()->count(),
            "totalLeads" => Das::find("status = " . Das::STATUS_LEAD)->count(),
            "topCouncils" => $councilsRows
        ]);

        $this->view->pick('admin/index/index');

    }

}
