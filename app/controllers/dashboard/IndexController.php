<?php

namespace Aiden\Controllers;

use Aiden\Models\Das;
use Aiden\Models\DasDocuments;
use Aiden\Models\DasPoiUsers;
use Aiden\Models\DasUsers;
use Aiden\Models\Poi;
use Aiden\Models\Users;
use Aiden\Models\Councils;
use Aiden\Models\UsersPhrases;

class IndexController extends _BaseController
{

    public function indexAction()
    {

        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();


        if($this->request->getQuery("vc")){
            // verify user
            $vc = $this->getUser()->getVerificationCode();
            if($vc == $this->request->getQuery("vc")){
                $this->getUser()->setVerified(true);
                $this->getUser()->save();
            }
        }

        $solution = $this->getUser()->getSolution();


        $this->view->setVars([
            'page_title' => 'Dashboard',
            'defaultDateRange' => array(date('m/d/Y', strtotime('-12 months')), date('m/d/Y', strtotime('+ 1 days'))),
            'mapboxKey' => $config->mapboxApiKey,
            'center' => [146.632891, -32.826687],
            'template' => Poi::TEMPLATE_DARK,
            'solution' => $solution
        ]);


        if($solution == 'monitor'){
            $this->view->pick('index/index-monitor');
        }else{
            $this->view->pick('index/index');
        }


    }

    public function getMetricsAction()
    {
        $userId = $this->getUser()->getId();
        $dateFilter = $this->request->getPost('dateFilter');
        if($dateFilter == 'year'){
            $from = date('Y-m-d', strtotime('-1 years'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }else{
            $from = date('Y-m-d', strtotime('-6 months'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }

        $toIncDec = date('Y-m-d', strtotime($this->request->getPost('to')));

        $solution = $this->getUser()->getSolution();
        $dateNow = date('Y-m-d');
        if($solution == 'search'){
            // Search
            $alertsData = array(
                'total' => DasUsers::getMetricsDasUsers($dateNow, $userId, false, false),
                'totalSaved' => DasUsers::getMetricsDasUsers($from, $userId, false, true),
                'incDec' => DasUsers::getMetricsIncDecAlerts($dateNow, $userId)
            );

        }else{
            // Assets
            $alertsData = array(
                'total' => DasPoiUsers::getMetricsDasPoiUsers($from, $to, $userId),
                'incDec' => DasPoiUsers::getMetricsIncDecAlerts($toIncDec, $userId)
            );
        }

        $return = array(
            'applications' => Das::getMetrics($from, $to, $userId),
            'councils' => Councils::getMetrics($from, $to, $userId),
            'documents' => array(
                'total' => DasDocuments::getMetricsDocuments($from, $to, $userId, $solution),
                'incDec' => DasDocuments::getMetricsIncDecDocuments($toIncDec, $userId, $solution),
            ),
            'assets' => Poi::getMetricsPoi($from, $to, $userId),
//            'phrases' => UsersPhrases::getMetricsPhrases($from, $to, $userId),
            'alerts' => $alertsData
        );

        return json_encode($return);
    }

    public function getDataAction()
    {
        $userId = $this->getUser()->getId();
        $action = $this->request->getPost('action');

        $dateFilter = $this->request->getPost('dateFilter');
        if($dateFilter == 'year'){
            $from = date('Y-m-d', strtotime('-1 years'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }else{
            $from = date('Y-m-d', strtotime('-30 days'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }


        $solution = $this->getUser()->getSolution();

        switch ($action) {
            case 'documents':
                $result = DasDocuments::getDataDocuments($from, $to, $userId, $solution);
                break;
            case 'alerts':
                $result = ($solution == 'search' ? DasUsers::getDataAlerts($from, $to, $userId) : DasPoiUsers::getDataAlerts($from, $to, $userId));
                break;
            case 'saved':
                $result = ($solution == 'search' ? DasUsers::getDataSavedAlerts($from, $to, $userId) : DasPoiUsers::getDataSavedAlerts($from, $to, $userId));
                break;
            case 'projects':
                $result = Das::getDataProjects($from, $to, $userId);
                break;
        }

        $data = array();

        $dataBuild = array();
        if($dateFilter == 'year'){
            $start    = (new \DateTime($from))->modify('first day of this month');
            $end      = (new \DateTime($to))->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $dataBuild[$dt->format("Y-m")] = 0;
            }
        }else{
            $start    = (new \DateTime($from));
            $end      = (new \DateTime($to));
            $interval = \DateInterval::createFromDateString('1 day');
            $period   = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $dataBuild[$dt->format("Y-m-d")] = 0;
            }
        }


        foreach ($result as $row) {
            if($dateFilter == 'year'){
                $format = 'Y-m';
            }else{
                $format = 'Y-m-d';
            }
            if(!isset($dataBuild[$row->created->format($format)])){
                $dataBuild[$row->created->format($format)] = 1;
            }else{
                $dataBuild[$row->created->format($format)] += 1;
            }
        }

        foreach ($dataBuild as $date => $count) {
            $data[] = [strtotime($date) * 1000, $count];
        }

        return json_encode($data);

    }

    public function getTableDataAction(){
        $dateFilter = $this->request->getPost('dateFilter');
        if($dateFilter == 'year'){
            $from = date('Y-m-d', strtotime('-1 years'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }else{
            $from = date('Y-m-d', strtotime('-6 months'));
            $to = date('Y-m-d', strtotime('+1 days'));
        }

        $sql = 'SELECT c.name(
            SELECT count(d.id) FROM das d WHERE d.council_id = c.id AND d.created >= "'.$from.'" AND d.created <= "'.$to.' AND (SELECT count(id) FROM das_documents WHERE id = d.id) > 0"
        ) as projectsCount ,
        (
            SELECT count(dd.id) FROM das_documents dd, das d WHERE d.council_id = c.id AND d.id = dd.das_id AND d.created >= "'.$from.'" AND d.created <= "'.$to.'"
        ) as docsCount,
        (
            SELECT SUM(estimated_cost) FROM das WHERE council_id = c.id AND created >= "'.$from.'" AND created <= "'.$to.'"
        ) as totalCost
        FROM councils c WHERE c.id != 1 AND c.id != 2 AND c.id != 8 AND c.id != 16 AND c.id != 33 
      
        ORDER BY `projectsCount` DESC';


        // get project count and cost

        $sql = 'SELECT c.name, d.id, d.estimated_cost, (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount 
                FROM das d, councils c
                WHERE d.council_id = c.id AND ((d.lodge_date >= "'.$from.'" AND d.lodge_date <= "'.$to.'") OR (d.created >= "'.$from.'" AND d.created <= "'.$to.'"))
                AND d.description NOT LIKE "%modification%"
                HAVING docCount > 0';

        $das = new Das();
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $data = [];
        foreach ($result as $row){

            if(isset($data[$row->name])){

                $data[$row->name] = [
                    'projects' => $data[$row->name]['projects'] + 1,
                    'totalCost' =>($row->estimated_cost != null ? $row->estimated_cost : 0) +  $data[$row->name]['totalCost'],
                    'documents' =>  $data[$row->name]['documents'] + $row->docCount
                ];

            }else{
                $data[$row->name] = [
                  'projects' => 1,
                  'totalCost' => ($row->estimated_cost != null ? $row->estimated_cost : 0),
                    'documents' => $row->docCount,
                ];
            }

//            $data[] = [
//              'name' => $row->name,
//                'projects' => $row->projectsCount,
//                'documents' => $row->docsCount,
//                'averageCost' => number_format(($row->totalCost != null || $row->totalCost != 0 ? $row->totalCost / $row->projectsCount : 0), 0),
//                'totalCost' => number_format(($row->totalCost != null ? $row->totalCost : 0), 0),
//            ];
        }

        return json_encode($data);
    }

    public function getSourcesAction()
    {
        $userId = $this->getUser()->getId();
        $from = date('Y-m-d', strtotime($this->request->getPost('from')));
        $to = date('Y-m-d', strtotime($this->request->getPost('to') . '+1 days'));

        $result = DasUsers::getTopSources($from, $to, $userId);

        $data = array();
        $totalL = count($result);
        foreach ($result as $row) {
            if(!isset($data[$row->name])){
                $data[$row->name] = 1;
            }else{
                $data[$row->name] += 1;
            }

        }

        $result = DasPoiUsers::getTopSources($from, $to, $userId);
        $totalP = count($result);
        foreach ($result as $row) {
            if(!isset($data[$row->name])){
                $data[$row->name] = 1;
            }else{
                $data[$row->name] += 1;
            }

        }

        $total = $totalL + $totalP;
        arsort($data);

        return json_encode(array(
            'total' => $total,
            'data' => $data
        ));
    }


    public function makeMeAdminAction()
    {

        $user = $this->getUser();
        $user->setLevel(Users::LEVEL_ADMINISTRATOR);
        if ($user->save()) {
            die("You are now an administrator.");
        } else {
            die("You are not an administrator, something went wrong.");
        }

    }

}
