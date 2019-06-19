<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 11/21/2018
 * Time: 9:44 AM
 */

namespace Aiden\Controllers;

use Aiden\Forms\EditLeadForm;
use Aiden\Models\Das;
use Aiden\Models\DasAddresses;
use Aiden\Models\DasDocuments;
use Aiden\Models\DasPhrases;
use Aiden\Models\DasUsers;
use Aiden\Models\Email;
use Aiden\Models\ErrorDocs;
use Aiden\Models\MetaData;
use Aiden\Models\Users;
use Aiden\Models\Billing;
use Aiden\Models\UsersPhrases;
use Aiden\Models\Poi;
use Aiden\Models\DasPoiUsers;
use Aiden\Models\UsersEmail;
use Aiden\Models\DasInvalidDocs;
use Aiden\Models\CamdenTask;
use Aiden\Models\WilloughbyTask;
use Aiden\Models\BaysideTask;
use Aiden\Models\CampbelltownTask;
require 'simple_html_dom.php';
class CronController extends _BaseController
{
    public function checkSubscriptionAction()
    {

        $dateNow = date('Y-m-d');
        //check trial users
        $user = new Users();
        $sql = 'SELECT id, name, last_name, email, created, reactivated, subscription_status FROM users WHERE subscription_status = "trial" OR subscription_status = "reactivated trial"';
        $results = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $user
            , $user->getReadConnection()->query($sql, [], [])
        );

        foreach ($results as $row) {

            $numDays = ($row->getsubscriptionStatus() == 'trial' ? MetaData::getMetaDataByTitle('Trial Period') : MetaData::getMetaDataByTitle('Reactivated Trial Period'));
            echo $numDays . ' <br>';
            $created = ($row->getsubscriptionStatus() == 'trial' ? $row->getCreated()->format('Y-m-d') : $row->getReactivated()->format('Y-m-d'));
            $createdPlus10 = date('Y-m-d', strtotime($created . '+'.$numDays.' days'));
            echo "Email: ".$row->getEmail()."<br>";
            echo "Date: ".$dateNow ."<br>";
            echo "Created: ".$row->getCreated()->format('Y-m-d') ."<br>";
            echo "Created+: ".$createdPlus10 ."<br>";
            echo "<hr>";
            if ($createdPlus10 <= $dateNow) {
                // trial expired
                $this->updateSubscriptionStatus($row->getId());

                // get users emails
                $emails = UsersEmail::find([
                    'conditions' => 'users_id = :usersId: AND type = :type:',
                    'bind' => [
                        'usersId' => $row->getId(),
                        'type' => 'subscription'
                    ]
                ]);

                $emailsTo = [];
                foreach ($emails as $em) {
                    if ($em->getEmail() != $user->getEmail()) {
                        $emailsTo[] = $em->getEmail();
                    }
                }

                // send email notification;
                   \Aiden\Models\Email::subscriptionExpirationNotification('trial', $row->getName(), $row->getLastName(), $row->getEmail() . ',' . implode(',', $emailsTo));

            }
        }

        $emails = null;
        $results = null;

        // check billing
//        $dateNow = date('Y-m-d', strtotime('+3 days'));
        $dateNow = date('Y-m-d');
        $billing = new Billing();
        $sql = 'SELECT b.id, b.users_id, u.name, u.last_name, u.email  
                FROM billing b, users u 
                WHERE b.users_id = u.id
                AND b.subscription_end_date <= "' . $dateNow . '"
                AND b.status = "canceled"';
        $results = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $billing
            , $billing->getReadConnection()->query($sql, [], [])
        );

        foreach ($results as $row) {

            $this->updateBillingStatus($row->getId(), $row->getUsersId());
            // send email notification;

            // get users emails
            $emails = UsersEmail::find([
                'conditions' => 'users_id = :usersId: AND type = :type:',
                'bind' => [
                    'usersId' => $row->getUsersId(),
                    'type' => 'subscription'
                ]
            ]);

            $emailsTo = [];
            foreach ($emails as $em) {
                if ($em->getEmail() != $user->getEmail()) {
                    $emailsTo[] = $em->getEmail();
                }
            }
            \Aiden\Models\Email::subscriptionExpirationNotification('expired', $row->name, $row->last_name, $row->email . ',' . implode(',', $emailsTo));
        }

        $emails = null;
        $results = null;

    }

    public function updateBillingStatus($id, $userId)
    {
        $billing = Billing::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                "id" => $id
            ]
        ]);
        if ($billing) {
            $billing->setStatus('expired');
            $billing->save();
            $this->updateSubscriptionStatus($userId);
        }

        $billing = null;

        return true;
    }

    public function updateSubscriptionStatus($userId)
    {
        $userEnt = Users::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                "id" => $userId
            ]
        ]);
        if ($userEnt) {
            $userEnt->setSubscriptionStatus('expired');
            $userEnt->save();

        }

        $userEnt = null;
        return true;
    }


    // send alerts email for new leads

    public function alertNotificationAction()
    {
//        error_reporting(E_ALL);
//        ini_set('display_errors', TRUE);
//        ini_set('display_startup_errors', TRUE);


        /* check phrase count is working */

        $das = new Das();
        // fetch all users first
        $users = Users::find([
            'conditions' => 'send_notifications_on_leads = 1'
        ]);
//        $users = Users::find(
//            [
//                'conditions' => 'id = :id:',
//                'bind' => [
//                    'id' => 31
//                ]
//            ]
//        );

        $di = \Phalcon\DI::getDefault();

        foreach ($users as $user) {
            $daIds = [];
            $councils = array();
            $usersId = $user->getId();
            echo $user->getEmail() . '<br>';
            $sql = "SELECT
                        d.id as dasId, d.council_reference, d.council_url, d.description,
                        c.id as councilId, c.name, du.users_phrase_id
                    FROM das d, das_users du, councils c
                    WHERE
                        d.id = du.das_id
                        AND d.council_id = c.id
                        AND du.email_sent = 0
                        AND du.users_id = " . $user->getId() . "
                        ";
            $result = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, [], [])
            );
            foreach ($result as $row) {
                $daIds[] = ['dasId' => $row->dasId, 'phraseId' => $row->users_phrase_id];
                $councils[$row->name]['das'][] = array(
                    'dasId' => $row->dasId,
                    'description' => $row->getHighlightedDescription($user->Phrases, false, [], true),
                    'reference' => $row->council_reference,
                    'addresses' => implode('<br>', $row->getAddresses($row->dasId, 1))
                );
            }


            if (count($result) > 0) {

                // get users emails
                $emails = UsersEmail::find([
                    'conditions' => 'users_id = :usersId: AND type = :type:',
                    'bind' => [
                        'usersId' => $usersId,
                        'type' => 'alerts'
                    ]
                ]);

                $emailsTo = [];
                foreach ($emails as $em) {
                    if ($em->getEmail() != $user->getEmail()) {
                        $emailsTo[] = $em->getEmail();
                    }
                }

                $view = $di->getView();
                $view->start();
                $view->setVars([
                    'BASE_URI' => BASE_URI,
                    'totalMatches' => count($result),
                    'councils' => $councils,
                    'totalCouncils' => count($councils),
                    'phrases' => ''
                ]);
                $view->setTemplateAfter('email'); // template name
                $view->render('controller', 'action');
                $view->finish();

                $emailHtml = $view->getContent();

                $config = $di->getConfig();
                $postFields = [
                    'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
                    'subject' => $config->mailgun->mailDigestSubject,
                    'html' => $emailHtml,
                    'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
                    'to' => $user->getEmail() . ',' . implode(',', $emailsTo)
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $config->mailgun->mailgunApiKey);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/' . $config->mailgun->mailgunDomain . '/messages');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$config->dev);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$config->dev);

                $output = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);


                // Error
                if ($info['http_code'] != 200) {

                    $message = sprintf('Could not send email, HTTP STATUS [%s]', $info['http_code']);
                    continue;
                }

                // Attempt to parse JSON
                $json = json_decode($output);
                if ($json === null) {
                    $message = 'Mailgun returned a non-Json response';
                    continue;
                }

                // After we've received confirmation from Mailgun, set matches as
                // processed so we only get fresh matches next time.
                if ($json->message == 'Queued. Thank you.') {
                    // set DA processed to true
                    for ($x = 0; $x < count($daIds); $x++) {
                        $daEntity = DasUsers::findFirst([
                            'conditions' => 'das_id = :das_id: AND users_id = :users_id: AND users_phrase_id = :phrase_id:',
                            'bind' => [
                                'das_id' => $daIds[$x]['dasId'],
                                'phrase_id' => $daIds[$x]['phraseId'],
                                'users_id' => $user->getId()
                            ]
                        ]);
                        if ($daEntity) {
                            $daEntity->setEmailSent(1);
                            $daEntity->save();
                        } else {
//                           var_dump($daEntity->getMessage());
                        }
                    }
                } else {
                    $message = print_r($json);
                }

            }

        }

        $di = null;
        $emails = null;
        $daEntity = null;
        $das = null;


    }

    // alert notification for POI

    public function alertPoiNotificationAction($fromImported = false, $poiIds = [])
    {
//        $fromImported = ($this->request->getQuery("imported") ? true : false);
//                error_reporting(E_ALL);
//        ini_set('display_errors', TRUE);
//        ini_set('display_startup_errors', TRUE);
        $das = new Das();
        $users = Users::find([
            'conditions' => 'send_notifications_on_leads = 1'
        ]);
//        $users = Users::find(
//            [
//                'conditions' => 'id = :id:',
//                'bind' => [
//                    'id' => 41
//                ]
//            ]
//        );
        $di = \Phalcon\DI::getDefault();
        $daIds = [];
        $config = $di->getConfig();


        // if from imported include filter
        $fromImportedSql = ($fromImported == true ? ' AND p.imported = 1 ' : '');

        foreach ($users as $user) {
            $poi = array();
            $poiAlpha = array();
            $poiBeta = array();
            $usersId = $user->getId();
            $sql = "SELECT 
                        d.id as dasId, d.council_reference, d.council_url, d.description,
                        c.id as councilId, c.name,
                        p.id as poiId, p.address, p.radius, p.type, p.name as poiName,
                        dp.distance, dp.users_poi_id
                    FROM das d, councils c, poi p, das_poi_users dp
                    WHERE d.id = dp.das_id
                    AND d.council_id = c.id
                    AND p.id = dp.users_poi_id
                    AND dp.email_sent = 0
                    $fromImportedSql
                    AND dp.users_id = " . $usersId;

            $result = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, [], [])
            );
            $alphaCount = 0;
            $betaCount = 0;
            foreach ($result as $row) {
                $daIds[] = [$row->dasId, $row->users_poi_id];
                $addressArr = explode(',', $row->address);
                if ($row->type == 1) {
                    $alphaCount++;
                    $poiAlpha[$row->poiName]['das'][] = array(
                        'dasId' => $row->dasId,
                        'usersPoiId' => $row->users_poi_id,
                        'description' => $row->description,
                        'reference' => $row->council_reference,
                        'addresses' => implode('<br>', $row->getAddresses($row->dasId)),
                        'distance' => bcdiv($row->distance, 1, 2) . 'km'
                    );
                } else {
                    $betaCount++;
                    $poiBeta[$row->poiName]['das'][] = array(
                        'dasId' => $row->dasId,
                        'usersPoiId' => $row->users_poi_id,
                        'description' => $row->description,
                        'reference' => $row->council_reference,
                        'addresses' => implode('<br>', $row->getAddresses($row->dasId)),
                        'distance' => bcdiv($row->distance, 1, 2) . 'km'
                    );
                }


            }


            $poi = ['Primary' => $poiAlpha, 'Secondary' => $poiBeta];

            if (count($result) > 0) {
                foreach ($poi as $key => $value) {
                    if (count($value) > 0) {

                        // get users emails
                        if ($key = 'Primary') {
                            $emails = UsersEmail::find([
                                'conditions' => 'users_id = :usersId: AND type = :type:',
                                'bind' => [
                                    'usersId' => $usersId,
                                    'type' => 'alerts'
                                ]
                            ]);
                        } else {
                            $emails = UsersEmail::find([
                                'conditions' => 'users_id = :usersId: AND type = :type:',
                                'bind' => [
                                    'usersId' => $usersId,
                                    'type' => 'alerts'
                                ]
                            ]);
                        }

                        $emailsTo = [];
                        foreach ($emails as $em) {
                            if ($em->getEmail() != $user->getEmail()) {
                                $emailsTo[] = $em->getEmail();
                            }
                        }

                        $view = $di->getView();
                        $view->start();
                        $view->setVars([
                            'BASE_URI' => BASE_URI,
                            'totalMatches' => ($key == 'Primary' ? $alphaCount : $betaCount),
                            'poi' => $value,
                            'totalPoi' => count($value),
                            'type' => $key
                        ]);
                        $view->setTemplateAfter('poi_email'); // template name
                        $view->render('controller', 'action');
                        $view->finish();

                        $emailHtml = $view->getContent();

                        $postFields = [
                            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
                            'subject' => $config->mailgun->mailDigestSubjectPoi,
                            'html' => $emailHtml,
                            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
                            'to' => $user->getEmail() . ',' . implode(',', $emailsTo)
                        ];


                        $email = Email::dailyNotificationEmail($config, $postFields);
                        if ($email == true) {
                            // set DA processed to true
                            for ($x = 0; $x < count($daIds); $x++) {
                                $daEntity = DasPoiUsers::findFirst([
                                    'conditions' => 'das_id = :das_id: AND users_id = :users_id: AND users_poi_id = :users_poi_id:',
                                    'bind' => [
                                        'das_id' => $daIds[$x][0],
                                        'users_poi_id' => $daIds[$x][1],
                                        'users_id' => $user->getId()
                                    ]
                                ]);
                                if ($daEntity) {
                                    $daEntity->setEmailSent(true);
                                    $daEntity->save();
                                } else {
//                                    var_dump($daEntity->getMessage());
                                }
                            }
                        } else {
                            continue;
                        }
                    }
                }

            }


        }


        if (count($poiIds) > 0) {
            // update poi to change imported = 0 to exclude on the next cron
            for ($up = 0; $up < count($poiIds); $up++) {
                $poiClass = Poi::findFirst($poiIds[$up]);
                if ($poiClass) {
                    $poiClass->setImported(0);
                    $poiClass->save();
                }
            }
        }

        $di = null;
        $emails = null;
        $daEntity = null;
        $das = null;
        $poiClass = null;
    }


    // fetch users matching phrases

    public function execScanPhraseAction()
    {
        $file = fopen("CRONLOCK", "r");
        $stat = fread($file, filesize("CRONLOCK"));
        $stat = 0;
        $fromDate = date('Y-m-d', strtotime('-12 months'));
        $dateNow = date('Y-m-d');
        if ($stat == 0) {
//            $file = fopen("CRONLOCK", "w");
//            fwrite($file, '1');
//            fclose($file);
            $up = UsersPhrases::find();
            foreach ($up as $row) {
                $userId = $row->getUserId();
                $user = Users::findFirst([
                   'conditions' => 'id = '.$userId
                ]);

                $fromDate = date('Y-m-d', strtotime($user->getCreated()->format('Y-m-d') . ' -6 months'));
                $phraseId = $row->getId();



                $phrase = $row->getPhrase();
                $caseSensitive = $row->getCaseSensitive();
                $searchAddresses = $row->getSearchAddresses();
                $literalSearch = $row->getLiteralSearch();
                $excludePhrase = $row->getExcludePhrase();
                $metadata = $row->getMetadata();
                $filterBy = ($row->getFilterBy() == 'all' ? 'all' : json_decode($row->getFilterBy()));
                $filterBy = ($filterBy != '' ? $filterBy : 'all');
                $filterBy = 'all';
                $councils = ($row->getCouncils() == 'all' ? 'all' : json_decode($row->getCouncils()));
                $costFrom = $row->getCostFrom();
                $costFrom = ($costFrom != '' ? $costFrom : 971763760);
                $costTo = $row->getCostTo();
                $costTo = ($costTo != '' ? $costTo : 0);
                $created = $row->getCreated()->format('Y-m-d');
                $new = $row->getNew();

                // include null if $costTo = 0;

                $includeNull = ($costFrom == 0 ? " OR d.estimated_cost IS NULL " : "");
                $costQuery = " AND ((d.estimated_cost >= " . $costFrom . " AND d.estimated_cost <= " . $costTo . ")" . $includeNull . ")";
                $councilsQry = '';
                if ($councils != 'all' AND $councils != '') {
                    $councilsQry = " AND (d.council_id = " . implode(' OR d.council_id = ', $councils) . ") ";
                }


                // if case sensitive make Like query in BINARY
                $caseSensitiveQuery = ($caseSensitive == true ? ' BINARY ' : '');
                $excludeQuery = ($excludePhrase == true ? ' NOT LIKE ' : ' LIKE ');
                if ($literalSearch == true) {
                    $excludeQuery = ($excludePhrase == true ? ' NOT RLIKE ' : ' RLIKE ');
                }

                // get excluded phrases
                $excludedPhrasesQuery = '';
                if($excludePhrase == false){
                    $ep = UsersPhrases::find([
                       'conditions' => 'users_id = :userId: AND exclude_phrase = 1',
                        'bind' => [
                            'userId' => $userId
                        ]
                    ]);


                    foreach($ep as $e){
                        $epqCaseSensitiveQuery = ($e->case_sensitive == true ? ' BINARY ' : '');
                        $epqExcludeQuery = ' NOT LIKE ';
                        $epqPhrase = $e->phrase;
                        if ($e->literal_search == true) {
                            $epqExcludeQuery = ' NOT RLIKE ';
                        }

                        $eqpFilter = ($e->literal_search == 'true' ? "[[:<:]]" . $epqPhrase . "[[:>:]]" : "%" . $epqPhrase . "%");
                        $excludedPhrasesQuery .= ' AND d.description ' .$epqExcludeQuery. $epqCaseSensitiveQuery . '"'.$eqpFilter.'"';
                    }
                }

                // metadata
                $metadataQuery = '';
                if ($metadata == true) {
                    $metadataQuery = ' AND d.estimated_cost > 0 ';
                }


                // doc query
                $docQuery = ' HAVING docCount > 0 ';

                // search addresses

                $searchAddressesQuery = '';
                if($searchAddresses == true){
                    $searchAddressesQuery = ' OR d.addresses_arr LIKE "%'.$phrase.'%"  ';
                }


                $filter = ($literalSearch == 'true' ? "[[:<:]]" . $phrase . "[[:>:]]" : "%" . $phrase . "%");
                // filter query by applicant
                $orAnd = ($excludePhrase == true ? " AND " : " OR ");
                $searchQuery = '';
                $searchFilterAll = true;
                if ($filterBy != 'all') {
                    if (!in_array('applicant', $filterBy) || !in_array('description', $filterBy)) {
                        if (in_array('applicant', $filterBy)) {
                            $filterByApplicant = ' AND p.role = "Applicant" ';
                            $searchFilterAll = false;
                            $searchQuery .= ' AND (p.name ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '")';
                        }
                        if (in_array('description', $filterBy)) {
                            $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '" '. $searchAddressesQuery .')';
                        }
                    } else {
                        $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '" '. $searchAddressesQuery .')';
                    }
                } else {
                    $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '" '. $searchAddressesQuery .')';
                }


                // no search query of phrase is blank
                if($phrase == '-'){
                    $searchQuery = '';
                }


                // if new filter query past 12months
                if($new == true){
                    $dateFilterQuery = 'AND d.lodge_date > "'.$fromDate.'"';
                }else{
                    $dateFilterQuery = 'AND d.lodge_date >= "'.$created.'"';
                }


                $das = new Das();
                if ($searchFilterAll == false) {
                    $sql = 'SELECT
                       d.id,
                       d.description,
                       p.name as applicantName,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_parties p
                WHERE d.council_id = c.id
                AND d.id = p.das_id
                ' . $filterByApplicant . '
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
              
                ' . $metadataQuery . '
                ' . $docQuery;
                } else {
                    $sql = 'SELECT
                       d.id,
                       d.description,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c
                WHERE d.council_id = c.id
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
               
                ' . $metadataQuery . ' 
                ' . $dateFilterQuery . $excludedPhrasesQuery . '
                ' . $docQuery;
                }

                $result = new \Phalcon\Mvc\Model\Resultset\Simple(
                    null
                    , $das
                    , $das->getReadConnection()->query($sql, [], [])
                );

                foreach ($result as $val) {
                    // check excluded phrases and overrides result of matched
//                    for($p = 0; $p < count($excludedPhrases); $p++){
//                        if(strpos($val->description, $excludedPhrases) === false){
//
//                        }
//                    }
                    // check if phrase and da already exists
                    if($phraseId){
                        if($phraseId != null){
                            $dasUsersCheck = DasUsers::findFirst([
                                'conditions' => 'das_id = :das_id: AND users_phrase_id = :users_phrase_id: AND users_id = :users_id:',
                                'bind' => [
                                    'das_id' => $val->getId(),
                                    'users_phrase_id' => $phraseId,
                                    'users_id' => $userId
                                ]
                            ]);

                            if ($dasUsersCheck == false) {
                                $dasPhrases = new DasPhrases();
                                $dasPhrases->setDasId($val->getId());
                                $dasPhrases->setPhraseId($phraseId);
                                $dasPhrases->setCreated(new \DateTime());

                                if (!$dasPhrases->save()) {
                                    var_dump($dasPhrases->getMessages());
                                }

                                // Create a relation between the development application and the user

                                $dasUsers = new DasUsers();
                                $dasUsers->setDasId($val->getId());
                                $dasUsers->setUserId($userId);
                                $dasUsers->setUsersPhraseId($phraseId);
                                $dasUsers->setStatus(DasUsers::STATUS_LEAD);
                                $dasUsers->setCreated(new \DateTime());
                                $dasUsers->setSeen(false);
                                $dasUsers->setEmailSent(false);
                                $dasUsers->setShowOnAlerts(true);

                                if (!$dasUsers->save()) {
                                    // DEBUG
                                    var_dump($dasUsers->getMessages());
                                } else {
                                    if($userId == 8){
                                        var_dump($phraseId);
                                    }
                                }
                            }
                        }

                    }
                }

                if($new == true){
                    $row->setNew(0);
                    $row->save();
                }

            }
//            $file = fopen("CRONLOCK", "w");
//            fwrite($file, '0');
//            fclose($file);
        } else {
            echo "Cron file locked, the crawler is already processing a phrase search";
        }

        $up = null;
        $user = null;
        $ep = null;
        $result = null;
        $dasUsersCheck = null;
        $dasUsers = null;
        $dasPhrases = null;

    }

    // MAPBOX API get address lat, lang

    public function getAddressLatLangAction()
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        // fetch list of addresses
        $addresses = DasAddresses::find([
            'conditions' => 'processed = :processed: LIMIT 100',
            'bind' => [
                'processed' => 0
            ]
        ]);

        if ($addresses) {
            foreach ($addresses as $row) {
                $addr = $row->getAddress();
                // check if address has matching lat, lang in existing matching address
                $ca = DasAddresses::findFirst([
                    'conditions' => 'address = :address: AND processed = :processed: AND (latitude != 0 OR latitude IS NOT NULL) ',
                    'bind' => [
                        'address' => $addr,
                        'processed' => true
                    ]
                ]);

                // address exist copy lat, lang else call api
                if ($ca) {
                    $row->setLatitude($ca->getLatitude());
                    $row->setLongitude($ca->getLongitude());
                    if ($row->getCleanAddress() != null) {
                        $row->setCleanAddress($ca->getCleanAddress());
                    }
                    $row->setProcessed(true);
                    $row->save();
                } else {
                    $addr = urlencode($addr);
                    $response = $this->curlToMapbox($addr, $config);
                    $response = json_decode($response);
                    echo $addr . ' <br>';
                    if (count($response->features) > 0) {
                        // get most relevant
                        $center = $response->features[0]->center;
                        $placeName = $response->features[0]->place_name;
                        $long = $center[0];
                        $lat = $center[1];

                        $row->setLatitude($lat);
                        $row->setLongitude($long);
                        $row->setProcessed(true);
                        $row->setCleanAddress($placeName);
                        $row->save();
                    } else {
                        $row->setLatitude('');
                        $row->setLongitude('');
                        $row->setProcessed(true);
                        $row->save();
                    }


                }


            }
        }


        // fetch address from poi without lat and lon

        $poi = Poi::find([
            'conditions' => 'latitude = 0 AND longitude = 0 AND processed = 0 LIMIT 100'
        ]);


        if ($poi) {
            foreach ($poi as $row) {
                $addr = $row->getAddress();
                $addr = urlencode($addr);
                $response = $this->curlToMapbox($addr, $config);
                $response = json_decode($response);

                echo $addr . ' <br>';
                if (count($response->features) > 0) {

                    // get most relevant
                    $center = $response->features[0]->center;

                    $long = $center[0];
                    $lat = $center[1];

                    $row->setLatitude($lat);
                    $row->setLongitude($long);
                    $row->setProcessed(true);

                    $row->save();
                } else {
                    $row->setLatitude('');
                    $row->setLongitude('');
                    $row->setProcessed(true);
                    $row->save();
                }

            }
        }

        $di = null;
        $addresses = null;
        $ca = null;
        $poi = null;
    }


    public function getCleanAddressAction()
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $addresses = DasAddresses::find([
            'conditions' => 'clean_processed = 0 AND clean_address IS NULL LIMIT 300'
        ]);

        if ($addresses) {
            foreach ($addresses as $row) {
                $row->setCleanProcessed(true);
                $addr = $row->getAddress();
                // check if address has matching lat, lang in existing matching address
                $ca = DasAddresses::findFirst([
                    'conditions' => 'address = :address: AND clean_address IS NOT NULL ',
                    'bind' => [
                        'address' => $addr
                    ]
                ]);

                if ($ca) {
                    if ($row->getCleanAddress() == null) {

                        $row->setCleanAddress($ca->getCleanAddress());
                        $row->save();
                    }
                } else {
                    $addr = urlencode($addr);
                    $response = $this->curlToMapbox($addr, $config);
                    $response = json_decode($response);
                    echo $addr . ' <br>';

                    if (count($response->features) > 0) {
                        // get most relevant
                        $placeName = $response->features[0]->place_name;
                        echo $placeName . '<br>';
                        echo ' ---------------------------------- <br>';
                        $row->setCleanAddress($placeName);

                    }
                }
                $row->save();
            }
        }
        $di = null;
        $ca = null;
        $addresses = null;
    }


    public function curlToMapbox($addr, $config)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mapbox.com/geocoding/v5/mapbox.places/" . $addr . ".json?access_token=" . $config->mapboxApiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 40d9d6dc-6e61-47f0-9508-34b9fcf8d76b",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        }

        return $response;
    }

    public function execScanPoiAction()
    {
        // get list of poi
        $poi = Poi::find();

        foreach ($poi as $row) {
            $usersId = $row->getUsersId();
            $lat = $row->getLatitude();
            $lon = $row->getLongitude();
            $rad = $row->getRadius();
            $minCost = $row->getMinCost();
            $maxCost = $row->getMaxCost();
            $metadata = $row->getMetadata();
            $type = $row->getType();
            $address = str_replace("'", '', $row->getAddress());
            //    if ($lat != 0 && $lon != 0) {
            if ($type == 1) {
                $das = Poi::getDaRadius($lat, $lon, $rad, $minCost, $maxCost, $metadata, $type, $row->getId());
            } else {
                $das = Poi::getDaSecondary($address, $minCost, $maxCost, $metadata, $type, $row->getId());
            }

            $newList = [];
            foreach ($das as $d) {
                // check later. remove in the table that doesnt include in the new list;
                $dasPoiUsers = DasPoiUsers::findFirst([
                    'conditions' => 'das_id = :das_id: AND users_id = :users_id: AND users_poi_id = :users_poi_id:',
                    'bind' => [
                        'das_id' => $d['dasId'],
                        'users_id' => $usersId,
                        'users_poi_id' => $row->getId()
                    ]
                ]);

                $newList[] = [
                    'dasId' => $d['dasId'],
                    'usersId' => $usersId,
                    'poiId' => $row->getId()
                ];


                if ($d['dasId'] == 8242) {
                    echo $d['dasId'] . ' ' . $usersId . ' ' . $row->getId() . '<br>';
                }
                if (count($dasPoiUsers) == 1) {

//                        echo $d['dasId'] . ' ' . $usersId . ' ' . $row->getId() . '<br>';
                }


                if ($dasPoiUsers == false) {

                    // get da distance from poi address
                    $distance = Poi::distance($d['latitude'], $d['longitude'], $lat, $lon, 'K');


                    // not exists add new
                    $dasPoiUsersNew = new DasPoiUsers();
                    $dasPoiUsersNew->setDasId($d['dasId']);
                    $dasPoiUsersNew->setUserId($usersId);
                    $dasPoiUsersNew->setUsersPoiId($row->getId());
                    $dasPoiUsersNew->setStatus(DasPoiUsers::STATUS_LEAD);
                    $dasPoiUsersNew->setCreated(new \DateTime());
                    $dasPoiUsersNew->setSeen(false);
                    $dasPoiUsersNew->setEmailSent(false);
                    $dasPoiUsersNew->setDistance($distance);
                    $dasPoiUsersNew->save();
                }

            }


            $conditionQry = 'users_poi_id = ' . $row->getId();
            foreach ($newList as $nl) {
                $conditionQry .= ' AND (das_id != ' . $nl['dasId'] . ' AND users_id = ' . $nl['usersId'] . ')';
            }

            $poiToDelete = DasPoiUsers::find([
                'conditions' => $conditionQry
            ]);

            if (count($poiToDelete) > 0) {
                $poiToDelete->delete();
            }
            //   }
        }

        $das = null;
        $poi = null;
        $poiToDelete = null;
        $dasPoiUsers = null;
    }


    public function execCSVPoiAction()
    {

        $poi = Poi::find([
            'conditions' => 'imported = :imported: AND imported_processed = :importedProcessed:',
            'bind' => [
                'imported' => true,
                'importedProcessed' => false
            ]
        ]);

        $poiIds = [];

        foreach ($poi as $row) {
            $poiIds[] = $row->getId();
            $usersId = $row->getUsersId();
            $lat = $row->getLatitude();
            $lon = $row->getLongitude();
            $rad = $row->getRadius();
            $minCost = $row->getMinCost();
            $maxCost = $row->getMaxCost();
            $metadata = $row->getMetadata();
            $type = $row->getType();
            $address = str_replace("'", '', $row->getAddress());
            //    if ($lat != 0 && $lon != 0) {
            if ($type == 1) {
                $das = Poi::getDaRadius($lat, $lon, $rad, $minCost, $maxCost, $metadata, $type, $row->getId());
            } else {
                $das = Poi::getDaSecondary($address, $minCost, $maxCost, $metadata, $type, $row->getId());
            }

            $newList = [];
            foreach ($das as $d) {
                // check later. remove in the table that doesnt include in the new list;
                $dasPoiUsers = DasPoiUsers::findFirst([
                    'conditions' => 'das_id = :das_id: AND users_id = :users_id: AND users_poi_id = :users_poi_id:',
                    'bind' => [
                        'das_id' => $d['dasId'],
                        'users_id' => $usersId,
                        'users_poi_id' => $row->getId()
                    ]
                ]);

                $newList[] = [
                    'dasId' => $d['dasId'],
                    'usersId' => $usersId,
                    'poiId' => $row->getId()
                ];


                if ($d['dasId'] == 8242) {
                    echo $d['dasId'] . ' ' . $usersId . ' ' . $row->getId() . '<br>';
                }
                if (count($dasPoiUsers) == 1) {

//                        echo $d['dasId'] . ' ' . $usersId . ' ' . $row->getId() . '<br>';
                }


                if ($dasPoiUsers == false) {

                    // get da distance from poi address
                    $distance = Poi::distance($d['latitude'], $d['longitude'], $lat, $lon, 'K');


                    // not exists add new
                    $dasPoiUsersNew = new DasPoiUsers();
                    $dasPoiUsersNew->setDasId($d['dasId']);
                    $dasPoiUsersNew->setUserId($usersId);
                    $dasPoiUsersNew->setUsersPoiId($row->getId());
                    $dasPoiUsersNew->setStatus(DasPoiUsers::STATUS_LEAD);
                    $dasPoiUsersNew->setCreated(new \DateTime());
                    $dasPoiUsersNew->setSeen(false);
                    $dasPoiUsersNew->setEmailSent(false);
                    $dasPoiUsersNew->setDistance($distance);
                    $dasPoiUsersNew->save();
                }

            }


            $conditionQry = 'users_poi_id = ' . $row->getId();
            foreach ($newList as $nl) {
                $conditionQry .= ' AND (das_id != ' . $nl['dasId'] . ' AND users_id = ' . $nl['usersId'] . ')';
            }

            $poiToDelete = DasPoiUsers::find([
                'conditions' => $conditionQry
            ]);

            if (count($poiToDelete) > 0) {
                $poiToDelete->delete();
            }
            //   }


            $row->setImportedProcessed(true);
            $row->save();
        }


        $this->alertPoiNotificationAction(true, $poiIds);

        $das = null;
        $poi = null;
        $poiToDelete = null;
        $dasPoiUsers = null;
        $dasPoiUsersNew = null;
        $distance = null;
        return true;
    }


    // PDF Documents
    public function scanDasDocumentsAction()
    {
        $pdf = new PdfController();
        $pdf->getDocumentUrlAction(40);
        $pdf = null;
    }

    // Scan PDF directory and send to Amazon S3

    public function scanPdfDirToAs3Action()
    {
        $pdf = new PdfController();
        $pdf->uploadToAmazonS3();
        $pdf = null;
    }





    public function fixParramattaAction()
    {
        $das = new Das();
        $sql = "SELECT dd.id, dd.url
                FROM das d, das_documents dd 
                WHERE d.id = dd.das_id
                AND d.council_id = 21";

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $replace = 'http://eplanning.parracity.nsw.gov.au';
        $str = 'http://hscenquiry.hornsby.nsw.gov.au';
        foreach ($result as $row) {
            $url = str_replace($str, $replace, $row->url);
            echo $url . '<br>';
            $ddas = DasDocuments::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $row->id
                ]
            ]);

            if ($ddas) {
                $ddas->setUrl($url);
                $ddas->save();
            }
        }

        $das = null;
        $ddas = null;
        $result = null;
    }


    public function checkPdfLinksAction()
    {
        $councilId = 9;
        $councilName = 'City of Sydney';

        $das = new Das();
        $sql = 'SELECT dd.* FROM `das_documents` dd, `das` d WHERE dd.das_id = d.id AND d.council_id = '.$councilId.' LIMIT 100';
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $count = 0;
        $ids = [];
        $dasIds = [];
        foreach ($result as $row){
            $url = $row->url;
            $id = $row->id;
            $dasId = $row->das_id;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "Postman-Token: 34b89c43-47fa-44df-9ab3-c9a8cdd0060f",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);


            switch ($councilName) {
                case 'City of Sydney':
                    if(strpos($response, 'AccessDenied') !== false){
                        echo $url . '<br>';
                        $ids[] = $id;
                        $dasIds[] = $dasId;
                        $count++;
                    }
                    break;
            }
        }

        if(count($ids) > 0){
            $sqlQuery = 'id = ' . implode(' OR id = ', $ids);
            $dd = DasDocuments::find([
                'conditions' => $sqlQuery
            ]);

            if(count($dd) > 0){
                $dd->delete();
            }
        }

        $dasIds = array_unique($dasIds);
        $dasIds = array_values($dasIds);

        for($i = 0; $i < count($dasIds); $i++){
            $did = DasInvalidDocs::findFirst([
               'conditions' => 'das_id = :dasId:',
                'bind' => [
                    'dasId' => $dasIds[$i]
                ]
            ]);


            if(!$did){
                $did = new DasInvalidDocs();
                $did->setDasId($dasIds[$i]);
                $save = $did->save();
                if(!$save){
                   var_dump($did->getMessages());
                }
            }
        }


        $das = null;
        $result = null;
        $dd = null;
        $did = null;
    }


    public function getDaDocsAction(){
        $das = new Das();

        $sql = 'SELECT d.* FROM das d, das_invalid_docs did
                WHERE did.das_id = d.id AND did.fixed = 0 LIMIT 30';

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        foreach ($result as $row){
            $url = $row->getCouncilUrl();
            $reference = $row->getCouncilReference();
            echo $reference . '<br>';
            echo $url . '<br>';
            $response = $this->curlToGet($url);
            if($response){
                $html = str_get_html($response);
                $docInfo = $html->find('#documents_info', 0);
                if($docInfo){
                    $li = $docInfo->find('.pdf');
                    if(count($li) > 0){
                        for($i = 0; $i < count($li); $i++){
                            $pdf = $li[$i]->find('a', 0);
                            if($pdf){
                                $docName = trim($pdf->innerText());
                                $docUrl = $pdf->getAttribute('href');
                                echo '-- '. $docName . '<br>';

                                // check if doc exist
                                $dd = DasDocuments::findFirst([
                                    'conditions' => 'das_id = :dasId: AND url = :url:',
                                    'bind' => [
                                        'dasId' => $row->getId(),
                                        'url' => $docUrl
                                    ]
                                ]);
                                if(!$dd){
                                    $dd = new DasDocuments();
                                    $dd->setDasId($row->getId());
                                    $dd->setName($docName);
                                    $dd->setUrl($docUrl);
                                    $dd->setDate(new \DateTime(date('Y-m-d')));
                                    $dd->save();
                                }
                            }
                        }
                    }

                }
            }

            $did = DasInvalidDocs::findFirst([
                'conditions' => 'das_id = :dasId:',
                'bind' => [
                    'dasId' => $row->getId()
                ]
            ]);

            if($did){
                $did->setFixed(1);
                $did->save();
            }
        }

        $das = null;
        $result = null;
        $did = null;
        $dd = null;
    }


    public function cleanUsersDaAction(){
        // remove all users da with phrase id of null
        $da = DasUsers::find([
            'conditions' => 'users_phrase_id IS NULL'
        ]);
        if($da){
            $da->delete();
        }
        $da = null;
    }


    public function checkDaDocsAction(){
//        $docs = DasDocuments::find([
//           'conditions' => 'checked = 0 AND as3_url is not null ORDER BY id DESC LIMIT 40',
//        ]);

        $dd = new DasDocuments();
        $sql = 'SELECT dd.* FROM `das_documents` dd, `das` d
                WHERE d.council_id = 20 
                AND dd.das_id = d.id
                AND dd.checked = 0
                AND dd.as3_url IS NOT NULL 
              
                ORDER BY dd.id DESC LIMIT 100';

        $docs = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $dd
            , $dd->getReadConnection()->query($sql, [], [])
        );

        if($docs){
            foreach ($docs as $row){
                $id = $row->getId();
                $url = trim($row->getAs3Url());
                echo $url . '<br>';
                $htmlData = $this->curlToGet($url);
                if(strpos($htmlData, '%PDF') === false){
                    $row->setCheckedStatus(0);
                    $row->setChecked(1);
                    $row->save();
                    // saved url for use later
                    $ed = ErrorDocs::findFirst([
                       'conditions' => 'doc_id = :docId:',
                        'bind' => [
                            'docId' => $id
                        ]
                    ]);
                    if(!$ed){
                        $ed = new ErrorDocs();
                    }
                    $ed->setDocId($id);
                    $ed->setAs3Url($url);
                    $ed->setFixed(0);
                    $ed->setStatus(0);
                    $ed->save();
                }else{
                    $row->setCheckedStatus(1);
                    $row->setChecked(1);
                    $row->save();

                    $ed = ErrorDocs::findFirst([
                        'conditions' => 'doc_id = :docId:',
                        'bind' => ['docId' => $id]
                    ]);
                    if($ed){
                        $ed->setFixed(1);
                        $ed->setStatus(2);
                        $ed->save();
                    }
                }
            }
        }

        $dd = null;
        $docs = null;
        $ed = null;
        return true;
    }


    public function rescanGetDaDocsAction(){
        $sql = 'select dd.*, ed.id as ed_id, d.council_id from das_documents dd, error_docs ed, das d WHERE d.council_id = 20 and dd.das_id = d.id and ed.doc_id = dd.id and ed.status = 0';
        $dasDoc = new DasDocuments();
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $dasDoc
            , $dasDoc->getReadConnection()->query($sql, [], [])
        );

        foreach ($result as $row){
            $ed = ErrorDocs::findFirst(['conditions' => 'id = '.$row->ed_id]);
            if($ed){
                $ed->setStatus(1);
                if($ed->save()){
                    $id = $row->id;
                    $as3url = $row->as3_url;
                    $docUrl = $row->url;
                    $councilId = $row->council_id;

                    echo $councilId . ' - ' . $as3url . '<br>';
                    switch ($councilId){
                        case 29: //Willoughby
                            $this->updateForRescan($id);
                            break;
                        case 20: // North Sydney
                            $this->updateForRescan($id);
                            break;
                    }
                }
            }

        }


        $result = null;
        $dasDoc = null;
        $ed = null;
        return true;

    }


    public function updateForRescan($id){
        $dd = DasDocuments::findFirst([
            'conditions' => 'id = '.$id
        ]);

        if($dd){
            $dd->setAs3Url('');
            $dd->setAs3Processed(0);
            $dd->setStatus(0);
            $dd->setChecked(0);
            $dd->save();
        }

        $dd = null;

        return true;
    }


    public function updateDasDataAction(){
        $date = date('Y-m');
        $dateFrom = date('Y-m-d', strtotime('-6 months'));
        $das = Das::find([
           'conditions' => 'checked = :checked: AND council_id = :councilId: AND lodge_date > :dateFrom: AND lodge_date < :date: OR lodge_date IS NULL ORDER BY id DESC LIMIT 50',
            'bind' => [
                'councilId' => 5,
                'checked' => 0,
                'date' => $date,
                'dateFrom' => $dateFrom
            ]
        ]);
        if($das){
            foreach ($das as $da){
                $da->setChecked(1);
                if($da->save()){
                    switch ($da->getCouncilId()){
                        case 5: // Camdem
                            $camden = new CamdenTask();
                            $camden->init(['data', 'documents'], $da);
                            $camden = null;
                            break;
                        case 6: // Campbelltown
                            $campbelltown = new CampbelltownTask();
                            $campbelltown->init(['data', 'documents'], $da);
                            $campbelltown = null;
                            break;
                        case 32: // Bayside
                            $bayside = new BaysideTask();
                            $bayside->init(['documents'], $da);
                            $bayside = null;
                            break;
                        case 29: // Willoughby
                            $willoughby = new WilloughbyTask();
                            $willoughby->init(['data'], $da);
                            $willoughby = null;
                            break;
                    }
                }
            }
        }

        $das = null;
        return true;
    }


    public function recordDaAddressAction(){
        $das = Das::find([
           'conditions' => 'addresses_arr IS NULL OR addresses_arr = "" LIMIT 3000'
        ]);

        foreach ($das as $da){
            $daddress = DasAddresses::find([
               'conditions' => 'das_id = :dasId:',
               'bind' => [
                   'dasId' => $da->getId()
               ]
            ]);

            if($daddress){
                $address = [];
                foreach ($daddress as $add){
                    $address[] = addslashes($add->getCleanAddress() !==  NULL ? $add->getCleanAddress() : $add->getAddress());
                }

                $da->setAddressesArr(json_encode($address));
                $da->save();
            }

        }

        $das = null;
        $daddress = null;
        return true;
    }



    public function updateUsersGoogleSheetsAction(){
        $gs = new GoogleSheetsController();
        $uu = $gs->updateUsers();
        $gs = null;
        return $uu;
    }


    public function curlToGet($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 34b89c43-47fa-44df-9ab3-c9a8cdd0060f",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if($err){
            var_dump($err);
        }
        curl_close($curl);

        return $response;
    }
}