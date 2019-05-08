<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 2/14/2019
 * Time: 6:46 AM
 */

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Das;
use Aiden\Models\DasUsers;
use Aiden\Models\Councils;


class NewsfeedController extends _BaseController
{

    public function indexAction() {
        if($this->getUser()->level == 1){
            if($this->getUser()->solution != 'search'){
                return json_encode('error');
            }
        }

        // Count results for pagination
        $page = ($this->request->getQuery('page', 'int') != null ? $this->request->getQuery('page', 'int') : 1);
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $das = new DasUsers();
        $sql = 'SELECT count(id) as totalCount FROM das_users WHERE users_id = '.$this->getUser()->getId();
        $dasForCount = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $totalPages = ceil($dasForCount[0]->totalCount / $limit);

        $das = new Das();
        $sql = 'SELECT d.id, d.council_reference, d.description,
                c.name, c.logo_url,
                du.users_phrase_id, du.created
                FROM das d, das_users du, councils c
                WHERE du.das_id = d.id
                AND d.council_id = c.id
                ORDER BY du.created DESC 
                LIMIT '.$offset.','.$limit;

        $results = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );


        $data = [];
        foreach($results as $row){

            $data[] = array(
              'id' => $row->getId(),
                'reference' => $row->council_reference,
                'council' => $row->name,
                'description' => $row->getHighlightedDescription($this->getUser()->Phrases),
                'created' => $row->getCreated()->format('d-m-Y'),
                'createdC' => $row->getCreated()->format('c'),
                'image' => $row->logo_url
            );
        }



        $this->view->setVars([
            'page_title' => 'Newsfeed',
            'page' => array(
                'current' => $page,
                'next' => $page + 1,
                'totalPages' => $totalPages,
            ),
            'data' => $data

        ]);

        $this->view->pick('newsfeed/newsfeed');

    }

}