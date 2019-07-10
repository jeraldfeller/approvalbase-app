<?php

namespace Aiden\Classes;

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;

class SecurityPlugin extends Plugin {

    public function getAcl() {

        //if (!isset($this->persistent->acl)) {
        if (true) {

            $acl = new AclList();

            $acl->setDefaultAction(Acl::DENY);

            // Register roles
            $roles = [
                'administrators' => new Role('Administrators'),
                'users' => new Role('Users'),
                'guests' => new Role('Guests')
            ];

            foreach ($roles as $role) {
                $acl->addRole($role);
            }

            // Administrator resources
            $adminResources = [
                'Aiden\Controllers\Admin:index' => ['*'],
                'Aiden\Controllers\Admin:leads' => ['*'],
                'Aiden\Controllers\Admin:users' => ['*'],
                'Aiden\Controllers\Admin:phrases' => ['*'],
                'Aiden\Controllers\Admin:councils' => ['*'],
                'Aiden\Controllers\Admin:datatables' => ['*'],
            ];

            // User resources
            $userResources = [
                'Aiden\Controllers:index' => ['index', 'getMetrics', 'getData', 'getSources', 'getTableData'],
                'Aiden\Controllers:search' => ['index', 'save', 'getDocumentsAndParties', 'saveDaNotes', 'saveClickedDas'],
                'Aiden\Controllers:leads' => [
                    'index', 'restore',
                    'view', 'indexSaved', 'save', 'unsave', 'archive',
                    'bulkMarkAsRead', 'bulkMarkAsUnread', 'bulkExportCsv', 'bulkSave', 'bulkUpdateLeadStatus',
                    'ajaxSave', 'getNewAlertCount'
                ],
                'Aiden\Controllers:newsfeed' => ['index'],
                'Aiden\Controllers:phrases' => ['index', 'create', 'delete', 'flipCase', 'flipLiteral', 'flipExclude', 'get'],
                'Aiden\Controllers:settings' => ['index', 'support', 'updateProfile', 'contact', 'billing', 'stripeApi', 'subscribe', 'updateSeen', 'notifications', 'notificationsUpdate', 'save', 'getUsersEmail', 'updateUsersEmail', 'cancelSubscription', 'setRestoreId'],
                'Aiden\Controllers:councils' => ['index', 'subscribe', 'unsubscribe', 'bulkSubscribe', 'bulkUnsubscribe'],
                'Aiden\Controllers:cron' => ['checkSubscription', 'alertNotification', 'alertPoiNotification', 'execScanPhrase', 'getAddressLatLang', 'getCleanAddress', 'execScanPoi', 'execCSVPoi', 'fixParramatta', 'getDaDocs', 'cleanUsersDa', 'checkDaDocs', 'updateDasData', 'rescanGetDaDocs', 'updateUsersGoogleSheets', 'recordDaAddress', 'resetCheckedAction', 'checkCouncilProjects', 'fixDocUrl', 'fixDocUrlSource'],
                'Aiden\Controllers:poi' => ['index', 'primary', 'secondary', 'alert', 'alertSaved', 'get', 'save', 'delete', 'getPoiAlerts', 'saveDa', 'import'],
                'Aiden\Controllers:pdf' => ['downloadPdf', 'download'],
                'Aiden\Controllers:helpers' => ['*'],

            ];

            // Guest resources
            $guestResources = [
                'Common\Controllers:login' => ['index', 'do', 'destroy'],
                'Common\Controllers:signup' => ['index', 'do', 'monitorSignup'],
                'Aiden\Controllers:users' => ['forgotPasswordIndex', 'sendForgotPasswordEmail', 'changePasswordRequestIndex', 'changePasswordConfirm'],
                'Aiden\Controllers:login' => ['index', 'do', 'destroy'],
                'Aiden\Controllers:signup' => ['index', 'do', 'monitorSignup'],

            ];
            // Global resources (everyone can access)
            $allAccessResources = [
                'Common\Controllers:index' => ['index'],
//                'Common\Controllers:pricing' => ['index'],
                'Aiden\Controllers:errors' => ['*'],
                'Aiden\Controllers:cron' => ['*'],
                'Aiden\Controllers:datatables' => ['*'],
                'Aiden\Controllers:index' => ['makeMeAdmin'], //REMOVE ON PROD
            ];

            // Register all resources
            foreach ($adminResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }
            foreach ($userResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }
            foreach ($guestResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }
            foreach ($allAccessResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            // Grant all roles access to All Access Resources
            foreach ($roles as $role) {
                foreach ($allAccessResources as $resource => $actions) {
                    $acl->allow($role->getName(), $resource, $actions);
                }
            }

            // Grant Administrators access to Administrator resources
            foreach ($adminResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Administrators', $resource, $action);
                }
            }

            // Grant Users access to User resources
            foreach ($userResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Users', $resource, $action);
                    $acl->allow('Administrators', $resource, $action);
                }
            }

            // Grant Guests access to Guest resources
            foreach ($guestResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Guests', $resource, $action);
                    $acl->allow('Users', $resource, $action);
                    $acl->allow('Administrators', $resource, $action);
                }
            }

            //The acl is stored in session, APC would be useful here too
            $this->persistent->acl = $acl;
        }

        return $this->persistent->acl;

    }

    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {

        // Check if user is authenticated
        $auth = $this->session->get('auth');

        if ($auth !== null) {
            switch ($auth['user']->level) {

                case \Aiden\Models\Users::LEVEL_ADMINISTRATOR:
                    $role = 'Administrators';
                    break;
                case \Aiden\Models\Users::LEVEL_USER:
                    $role = 'Users';
                    break;
                default:
                    $role = 'Guests';
                    break;
            }
        }
        else {

            $role = 'Guests';
        }

        $namespace = $dispatcher->getNamespaceName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $acl = $this->getAcl();
        // If resource doesn't exist
        $bResourceExists = $acl->isResource($namespace . ':' . $controller);
        if (!$bResourceExists) {

            $dispatcher->forward([
                'namespace' => 'Aiden\Controllers',
                'controller' => 'errors',
                'action' => 'show404',
                'params' => [
                    'namespace' => $namespace,
                    'controller' => $controller,
                    'action' => $action,
                    'role' => $role
                ]
            ]);
            return false;
        }

        // If resource is protected and user hasn't got enough permissions
        $bIsAllowed = $acl->isAllowed($role, $namespace . ':' . $controller, $action);
        if (!$bIsAllowed) {

            // If user isn't logged in, redirect to login page.
            if ($role === 'Guests') {
                if($action != 'changePasswordIndex'){
                    $this->flashSession->notice('Please login to continue.');
                    $this->response->redirect('login', false, 302);
                    $this->view->disable();
                    return false;
                }
            }
            else {

                $dispatcher->forward([
                    'namespace' => 'Aiden\Controllers',
                    'controller' => 'errors',
                    'action' => 'show401',
                    'params' => [
                        'namespace' => $namespace,
                        'controller' => $controller,
                        'action' => $action,
                        'role' => $role
                    ]
                ]);
                return false;
            }
        }

    }

}
