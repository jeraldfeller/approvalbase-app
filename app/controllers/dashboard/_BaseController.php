<?php

namespace Aiden\Controllers;

use Aiden\Models\Users;
use Aiden\Models\Das;
use Aiden\Models\DasUsers;
class _BaseController extends \Phalcon\Mvc\Controller {

    private $user;

    public function initialize() {
        $this->setUrlVariables();



        if ($this->session->has('auth')) {
            $user = $this->session->get("auth")["user"];
            $this->user = $user;
            $this->view->setVar("loggedInUser", $user);
            $this->setSideVariables();

            $users = Users::getUsersInfoById($this->getUser()->getId());
            $this->view->setVars([
                'user'			=> $users
            ]);

            // check user subscription status
            if($users['level'] != 'Administrator'){
                $getKeys = $this->request->getQuery();
                if($getKeys['_url'] != '/billing' AND $getKeys['_url'] != '/billing/stripeApi'){
                    $subscriptionStatus = $users['subscriptionStatus'];
                    if($subscriptionStatus == 'expired'){
                        $this->flashSession->error('Your trial period has expired');
                        return $this->response->redirect('billing', false, 302);
                    }
                }

            }
        }

    }

    public function afterExecuteRoute() {
        $this->view->setViewsDir($this->getDI()->get('config')->directories->viewsDirDashboard);

    }

    public function setSideVariables() {

        // Administrators only
        if ((int) $this->getUser()->getLevel() === Users::LEVEL_ADMINISTRATOR) {

            // Pass numbers for sidemenu
            $leadTypes = [
                Das::STATUS_LEAD
            ];

            foreach ($leadTypes as $leadType) {

                $numLeads = Das::find([
                            'conditions' => 'status = :status:',
                            'bind' => [
                                'status' => $leadType
                            ]
                        ])
                        ->count();

                $this->view->setVar('adminLeads_' . $leadType, $numLeads);
            }
        }

        // Everything higher than registered/verified user
        if ((int) $this->getUser()->getLevel() >= Users::LEVEL_USER) {

            $grandTotal = $this->getUser()->getDasByStatus([DasUsers::STATUS_LEAD, DasUsers::STATUS_SAVED])->count();
            $savedTotal = $this->getUser()->getDasByStatus(DasUsers::STATUS_SAVED)->count();

            $this->view->setVars([
                "userTotalLeads_" . DasUsers::STATUS_LEAD => $grandTotal,
                "userTotalLeads_" . DasUsers::STATUS_SAVED => $savedTotal
            ]);
        }

    }

    public function setUrlVariables() {

        // These keys in $this->request->getQuery() should be skipped.
        $skipKeys = [
            '_url',
            'page' // This bit will be added in pagination.volt
        ];

        $parameters = ''; // Everything after the hostname.domain
        $getKeys = $this->request->getQuery(); // $_GET
        $numOfAddedParameters = 0;

        // Rebuild the URL (without $skipKeys)
        foreach ($getKeys as $key => $value) {

            if (is_array($key) || is_array($value)) {
                continue;
            }

            if (!in_array($key, $skipKeys)) {
                $parameters .= '&' . urlencode($key) . '=' . urlencode($value);
                $numOfAddedParameters++;
            }
        }

        // Base URL is https://counts.secondyear.com.au[/:controller[/:action[/:params]]]?
        // It's basically everything _BUT_ $_GET parameters
        //$baseUrl = $this->url->getBaseUri() . ltrim($getKeys['_url'], '/');
        $baseUrl = isset($getKeys['_url']) ? $this->url->getBaseUri() . ltrim($getKeys['_url'], '/') : $this->url->getBaseUri();


        $completeUrl = $baseUrl;
        if ($numOfAddedParameters > 0) {
            $completeUrl .= '?' . ltrim($parameters, '&');
        }

        $this->view->setVar('_url', [
            'amountOfGetParams' => $numOfAddedParameters,
            'baseUrl' => $baseUrl,
            'completeUrl' => $completeUrl,
            'searchUrl' => str_replace('/search', '', $baseUrl),
        ]);

    }

    public function getUser() {
        return $this->user;

    }

}
