<?php

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\Users;
use Aiden\Models\Admin;
use Aiden\Models\MetaData;
use Aiden\Models\Billing;
use Aiden\Models\UsersEmail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Token;
use Mailgun\Mailgun;


class SettingsController extends _BaseController
{

    public function indexAction()
    {
        $this->view->setVars([
            'page_title' => 'Profile settings',
            'unix' => time()
        ]);

    }

    public function supportAction()
    {
        $this->view->setVars([
            'page_title' => 'Contact form'
        ]);
        $this->view->pick('settings/index');
    }

    public function billingAction()
    {

        // get billing
        $billing = $this->getUsersBilling();
        $this->view->setVars([
            'page_title' => 'Billing',
            'current' => $billing['current'],
            'invoices' => $billing['invoices'],
            'stripeApiKey' => Admin::getApiKeyBySource(STRIPE_ENV)['apiKey'],
            'subscriptionCost' => MetaData::getMetaDataByTitle('subscription_cost')
        ]);
        $this->view->pick('settings/index');
    }

    public function notificationsAction()
    {
        $this->view->setVars([
            'page_title' => 'Notifications',
            'solution' => $this->getUser()->getSolution()
        ]);
        $this->view->pick('settings/index');
    }

    public function saveAction()
    {
        $action = $this->request->getQuery("ajax");
        if ($action == 1) {
            return $this->dispatcher->forward(["action" => "updateProfile"]);
        } else if ($action == 2) {
            return $this->dispatcher->forward(["action" => "contact"]);
        } else {
            return false;
        }

    }

    public function updateProfileAction()
    {
        $userId = $this->getUser()->getId();
        $filePath = '';
        $basePath = '';
        if (isset($_FILES['avatar'])) {
            if ($_FILES['avatar']['name'] != '') {
                $source = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                //allowed extensions
                $imageExt = array('png', 'jpg', 'jpeg', 'gif');
                if (in_array($ext, $imageExt)) {
                    $filePath = BASE_URI . "dashboard_assets/images/avatars/" . $userId . "." . $ext;
                    $basePath = "dashboard_assets/images/avatars/" . $userId . "." . $ext;
                    $targetPath = __DIR__ . '/../../../public/dashboard_assets/images/avatars/' . $userId . '.' . $ext;
                    if (move_uploaded_file($source, $targetPath)) {
//                         $this->correctImageOrientation($targetPath);
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            }
        }
        $data = array(
            'firstName' => trim($this->request->getPost('firstName')),
            'lastName' => trim($this->request->getPost('lastName')),
            'websiteUrl' => trim($this->request->getPost('websiteUrl')),
            'companyName' => trim($this->request->getPost('companyName')),
            'companyCity' => trim($this->request->getPost('companyCity')),
            'companyCountry' => trim($this->request->getPost('companyCountry')),
            'avatar' => $filePath,
            'avatarBasePath' => $basePath
        );
        return json_encode(Users::updateUserInfo($data, $userId));
    }

    function correctImageOrientation($filename) {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            var_dump($exif);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    // then rewrite the rotated image back to the disk as $filename
                    imagejpeg($img, $filename, 95);
                } // if there is some rotation necessary
            } // if have the exif orientation info
        } // if function exists
    }

    public function notificationsUpdateAction()
    {
        if ($this->request->getPost('action') == 'show_alerts') {
            $this->getUser()->setShowAlerts($this->request->getPost('value'));
        } else {
            $this->getUser()->setSendNotificationsOnLeads($this->request->getPost('value'));
        }

        $result = $this->getUser()->save();

        return json_encode($result);

    }

    public function getUsersEmailAction()
    {

        $userId = $this->getUser()->getId();
        $solution = $this->getUser()->getSolution();
        $columnClass = ($this->request->getQuery('columnClass') ? $this->request->getQuery('columnClass') : 'col-sm-7');
        $primaryEmails = UsersEmail::find([
            'conditions' => 'users_id = :usersId:',
            'bind' => [
                'usersId' => $userId
            ]
        ]);

        $alerts = '';
        $billing = '';
        $share = '';

        $alertsCount = '';
        $billingCount = '';
        $shareCount = '';
        foreach ($primaryEmails as $p) {
            switch ($p->getType()) {
                case 'alerts':
                    $alerts .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                                        <div class="">
                                            <input data-type="alerts" data-id="' . $p->getId() . '" value="' . $p->getEmail() . '" type="email" class="form-control input-email" placeholder="email address" />
                                        </div>
                                    </div>';
                    $alertsCount++;
                    break;
                case 'billing':
                    $billing .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                                        <div class="">
                                            <input data-type="billing" data-id="' . $p->getId() . '" value="' . $p->getEmail() . '" type="email" class="form-control input-email" placeholder="email address" />
                                        </div>
                                    </div>';
                    $billingCount++;
                    break;
                case 'share':
                    $share .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                                        <div class="">
                                            <input data-type="share" data-id="' . $p->getId() . '" value="' . $p->getEmail() . '" type="email" class="form-control input-email" placeholder="email address" />
                                        </div>
                                    </div>';
                    $shareCount++;
                    break;

            }

        }


        while ($alertsCount != 5) {
            $alerts .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                        <div class="">
                            <input data-type="alerts" data-id="0" type="email" class="form-control input-email" placeholder="email address" />
                        </div>
                    </div>
                ';
            $alertsCount++;
        }

        while ($billingCount != 5) {
            $billing .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                        <div class="">
                            <input data-type="billing" data-id="0" type="email" class="form-control input-email" placeholder="email address" />
                        </div>
                    </div>
                ';
            $billingCount++;
        }

        while ($shareCount != 1) {
            $share .= '<div class="'.$columnClass.' no-pdd-left mrg-top-10">
                        <div class="">
                            <input data-type="share" data-id="0" type="email" class="form-control input-email" placeholder="email address" />
                        </div>
                    </div>
                ';
            $shareCount++;
        }

        return json_encode([
            'alerts' => $alerts,
            'billing' => $billing,
            'share' => $share
        ]);

    }

    public function updateUsersEmailAction()
    {
        $userId = $this->getUser()->getId();
        $data = $this->request->getPost('data');

        foreach ($data as $row) {
            $id = $row['id'];
            $type = $row['type'];
            $email = $row['email'];
            // check if has id and update email.
            $ue = UsersEmail::findFirst([
                'conditions' => 'id = ' . $id
            ]);
            if ($ue) {
                if ($email != '') {
                    $ue->setEmail($email);
                    $ue->save();
                } else {
                    $ue->delete();
                }
            } else {
                // new email
                if ($email != '') {
                    $ue = new UsersEmail();
                    $ue->setUserId($userId);
                    $ue->setEmail($email);
                    $ue->setType($type);
                    $ue->save();
                }
            }
        }
        return json_encode(true);

    }

    public function contactAction()
    {
        $userId = $this->getUser()->getId();
        $email = $userId = $this->getUser()->getEmail();
        $subject = trim($this->request->getPost('subject'));
        $message = trim($this->request->getPost('message'));
        $name = $userId = $this->getUser()->getName() . ' ' . $userId = $this->getUser()->getLastName();
        $response = \Aiden\Models\Email::contactFormEmail($subject, $message, $email, $name);
        return json_encode($response);
    }


    public function stripeApiAction()
    {
        $token = $this->request->getPost('token');
        $amount = MetaData::getMetaDataByTitle('subscription_cost').'00';
        $stripe = new Stripe();
        $stripe::setApiKey(Admin::getApiKeyBySource('stripe')['secretKey']);

        // create customer
        if ($this->getUser()->getStripeCustomerId() == '') {
            $customer = Customer::create([
                'email' => $this->getUser()->getEmail(),
                'source' => $token
            ]);
            $customerId = $customer->id;
            $this->getUser()->setStripeCustomerId($customerId);
            $this->getUser()->save();
        } else {
            $customerId = $this->getUser()->getStripeCustomerId();
        }
        // create charge;
        $charge = Charge::create([
            'amount' => $amount,
            'currency' => 'usd',
            'description' => 'Monthly subscription',
            'customer' => $customerId,
            'metadata' => ['userId' => $this->getUser()->getId()]
        ]);

        if ($charge->status == 'succeeded') {
            $date = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime('+30 days'));
            $billing = new Billing();
            $billing->setUsersId($this->getUser()->getId());
            $billing->setChargeId($charge->id);
            $billing->setDateCreated(new \DateTime($date));
            $billing->setAmount($amount / 100);
            $billing->setSubscriptionStartDate(new \DateTime($date));
            $billing->setSubscriptionEndDate(new \DateTime($endDate));
            $billing->setStatus('active');
            if ($billing->save()) {
                $this->getUser()->setSubscriptionStatus('active');
                $this->getUser()->save(0);

                // sent email notification
                // get users emails
                $emails = UsersEmail::find([
                    'conditions' => 'users_id = :usersId: AND type = :type:',
                    'bind' => [
                        'usersId' => $this->getUser()->getId(),
                        'type' => 'billing'
                    ]
                ]);

                $emailsTo = [];
                foreach ($emails as $em){
                    if($em->getEmail() != $this->getUser()->getEmail()){
                        $emailsTo[] = $em->getEmail();
                    }
                }
                \Aiden\Models\Email::subscriptionEmailNotification($this->getUser()->getName(), $this->getUser()->getLastName(), $this->getUser()->getEmail().','.implode(',', $emailsTo), $amount / 100, $charge->id, $charge->source->last4);
            }
            return json_encode(true);
        } else {
            return json_encode(false);
        }
    }


    public function subscribeAction(){
        $token = $this->request->getPost('token');
        $amount = MetaData::getMetaDataByTitle('subscription_cost').'00';
        $stripe = new Stripe();
        $stripe::setApiKey(Admin::getApiKeyBySource(STRIPE_ENV)['secretKey']);


        // create customer
        if ($this->getUser()->getStripeCustomerId() == '') {
            $customer = Customer::create([
                'email' => $this->getUser()->getEmail(),
                'name' => $this->getUser()->getName(). ' ' . $this->getUser()->getLastName(),
                'source' => $token
            ]);
            $customerId = $customer->id;
            $this->getUser()->setStripeCustomerId($customerId);
            $this->getUser()->save();
        } else {
            $customerId = $this->getUser()->getStripeCustomerId();
        }


        // create subscription;
        $subscription = Subscription::create([
            "customer" => $customerId,
            "billing" => "charge_automatically",
            "items" => [
                [
                    "plan" => MetaData::getMetaDataByTitle('subscription_plan_id')
                ],
            ]
        ]);


        if ($subscription->status == 'active') {
            $date = date('Y-m-d H:i:s', $subscription->current_period_start);
            $endDate = date('Y-m-d H:i:s', $subscription->current_period_end);

            // retrieve invoice
            $invoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);

            $billing = new Billing();
            $billing->setUsersId($this->getUser()->getId());
            $billing->setChargeId($subscription->id);
            $billing->setInvoiceId($subscription->latest_invoice);
            $billing->setInvoice('18801-'.$invoice->number);
            $billing->setDateCreated(new \DateTime($date));
            $billing->setAmount($amount / 100);
            $billing->setSubscriptionStartDate(new \DateTime($date));
            $billing->setSubscriptionEndDate(new \DateTime($endDate));
            $billing->setStatus('active');
            if ($billing->save()) {
                $this->getUser()->setSubscriptionStatus('active');
                $this->getUser()->save(0);

                // sent email notification
                // get users emails
                $emails = UsersEmail::find([
                    'conditions' => 'users_id = :usersId: AND type = :type:',
                    'bind' => [
                        'usersId' => $this->getUser()->getId(),
                        'type' => 'billing'
                    ]
                ]);

                $emailsTo = [];
                foreach ($emails as $em){
                    if($em->getEmail() != $this->getUser()->getEmail()){
                        $emailsTo[] = $em->getEmail();
                    }
                }
//                \Aiden\Models\Email::subscriptionEmailNotification($this->getUser()->getName(), $this->getUser()->getLastName(), $this->getUser()->getEmail().','.implode(',', $emailsTo), $amount / 100, '18801-'.$invoice->number, '');
            }
            return json_encode(true);
        } else {
            return json_encode(false);
        }
    }

    public function getUsersBilling()
    {
        $billing = new Billing();
        $sql = 'SELECT b.*, u.name, u.last_name FROM `billing` b, `users` u WHERE u.id = b.users_id AND b.users_id = ' . $this->getUser()->getId() . ' ORDER BY b.id DESC LIMIT 1';
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $billing
            , $billing->getReadConnection()->query($sql, [], [])
        );

        $invoices = array();
        $current = array();
        $x = 0;
        foreach ($result as $row) {
            if ($x == 0) {
                $current = array(
                    'id' => $row->getId(),
                    'chargeId' => $row->getChargeId(),
                    'invoice' => $row->getInvoice(),
                    'startDate' => $row->getSubscriptionStartDate()->format('M d, Y'),
                    'endDate' => $row->getSubscriptionEndDate()->format('M d, Y'),
                    'status' => ucfirst($row->getStatus()),
                    'amount' => $row->getAmount(),
                    'paymentDate' => $row->getDateCreated()->format('M d, Y'),
                    'firstName' => $row->name,
                    'lastName' => $row->last_name
                );
            }
            $invoices[] = array(
                'id' => $row->getId(),
                'chargeId' => $row->getChargeId(),
                'invoice' => $row->getInvoice(),
                'startDate' => $row->getSubscriptionStartDate()->format('M d, Y'),
                'endDate' => $row->getSubscriptionEndDate()->format('M d, Y'),
                'status' => ucfirst($row->getStatus()),
                'amount' => $row->getAmount(),
                'paymentDate' => $row->getDateCreated()->format('M d, Y'),
                'firstName' => $row->name,
                'lastName' => $row->last_name
            );
            $x++;
        }

        return array(
            'invoices' => $invoices,
            'current' => $current
        );

    }


    public function cancelSubscriptionAction(){
        $userId = $this->getUser()->getId();
        $this->getUser()->setSubscriptionStatus('canceled');
        $stripe = new Stripe();
        $stripe::setApiKey(Admin::getApiKeyBySource(STRIPE_ENV)['secretKey']);

        if($this->getUser()->save()){
            $billing = Billing::findFirst([
               'conditions' => 'users_id = :userId: ORDER BY id DESC',
                'bind' => [
                    'userId' => $userId
                ]
            ]);

            if($billing){
                $billing->setStatus('canceled');
                $billing->save();

                // cancel stripe subscription
                $subId = $billing->getChargeId();
                $subscription = Subscription::update(
                    $subId,
                    [
                        'cancel_at_period_end' => true
                    ]
                );
                return json_encode(true);
            }else{
                return json_encode(false);
            }
        }else{
            return json_encode(false);
        }

    }


    public function updateSeenAction()
    {
        $v = $this->request->getPost('v');
        switch ($v){
            case 'search':
                $this->getUser()->setSeenModal(1);
                break;
            case 'alerts':
                $this->getUser()->setOnboardingAlerts(1);
                break;
            case 'filter':
                $this->getUser()->setOnboardingFilter(1);
                break;
            case 'all':
                $this->getUser()->setSeenModal(1);
                $this->getUser()->setOnboardingAlerts(1);
                $this->getUser()->setOnboardingFilter(1);
                break;
        }

        $this->getUser()->save();
        return true;
    }


    public function resetOnboardingAction(){
        $user = $this->getUser();
        $user->setSeenModal(0);
        $user->setOnboardingAlerts(0);
        $user->setOnboardingFilter(0);
        $user->save();

        echo json_encode(true);


    }

    public function setRestoreIdAction(){
        $restoreId =  $this->request->getPost('restoreId');
        $user = $this->getUser();
        $user->setRestoreId($restoreId);
        $user->save();
        $user = null;

        return true;

    }

}
