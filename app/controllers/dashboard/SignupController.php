<?php

namespace Aiden\Controllers;

use Aiden\Models\Councils;
use Aiden\Models\UsersEmail;

class SignupController extends _BaseController {

    public function indexAction() {

        if ($this->session->has('auth')) {
            return $this->response->redirect('dashboard', false, 302);
        }
        $this->view->setVars([
            'form' => new \Aiden\Forms\RegisterForm(),
            'user'  => $this->getUser(),
            'page_title' => 'Sign Up Free Trial Account | ApprovalBase',
            'solution' => 'search',
            'preFilledEmail' => ($this->request->getQuery('email') ? $this->request->getQuery('email') : '')
        ]);


    }

    public function monitorSignupAction() {

        if ($this->session->has('auth')) {
            return $this->response->redirect('dashboard', false, 302);
        }
        $this->view->setVars([
            'form' => new \Aiden\Forms\RegisterForm(),
            'user'  => $this->getUser(),
            'page_title' => 'Sign Up Free Trial Account | ApprovalBase',
            'solution' => 'monitor'
        ]);

        $this->view->pick('signup/index');

    }

    public function doAction() {

        // Check if POST request.
        if ($this->request->isPost() === false) {
            $this->flashSession->error('Invalid request');
            return $this->response->redirect('signup', false, 302);
        }

        // Validate the form
        $form = new \Aiden\Forms\RegisterForm();
        if (!$form->isValid($this->request->getPost())) {
            foreach ($form->getMessages() as $message) {
                $this->flashSession->error($message);
                return $this->response->redirect($this->dispatcher->getControllerName(), false, 302);
            }
        }
        $verificationCode = $this->generateRandomString();
        $name = $this->request->getPost("name", "string");
        $lname = $this->request->getPost("lname", "string");
        $email = $this->request->getPost("email", "string");
        $password = $this->request->getPost("password");
        $websiteUrl = $this->request->getPost("websiteUrl", "string");
        $companyName = $this->request->getPost("companyName", "string");
        $companyCountry = $this->request->getPost("companyCountry", "string");
        $companyCity = $this->request->getPost("companyCity", "string");
        $solution = $this->request->getPost("solution", "string");
        $iAgree = $this->request->getPost("iAgree", "string");
        $checkfirst = \Aiden\Models\Users::findFirst(array(
            "email = ?1",
            "bind" => array(
                1 => $email,
            )));

        if ($checkfirst) {
            $errorMessage = 'Email is already existed, please try another.';
            $this->flashSession->error($errorMessage);
            return $this->response->redirect('signup', false, 302);
        }

        if($iAgree != 'iAgree'){

            $this->flashSession->error('Please check that I Agree to continue');
            return $this->response->redirect('signup', false, 302);
        }
        $user = new \Aiden\Models\Users();
        $user->setName($name);
        $user->setLastName($lname);
        $user->setEmail($email);
        $user->setWebsiteUrl($websiteUrl);
        $user->setCompanyName($companyName);
        $user->setCompanyCountry($companyCountry);
        $user->setCompanyCity($companyCity);
        $user->setPasswordHash($this->security->hash($password));
        $user->setLevel(\Aiden\Models\Users::LEVEL_USER);
        $user->setCreated(new \DateTime());
        $user->setLastLogin(new \DateTime());
        $user->setImageUrl(BASE_URI."dashboard_assets/images/avatars/avatar.jpg");
        $user->setSeenModal(0);
        $user->setPhraseDetectEmail(0);
        $user->setSubscriptionStatus('trial');
        $user->setSendNotificationsOnLeads(true);
        $user->setShowAlerts(true);
        $user->setSolution($solution);
        $user->setVerified(false);
        $user->setVerificationCode($verificationCode);

        if ($user->save()) {


            // add email to users email
            $ue = new UsersEmail();
            $ue->setUserId($user->getId());
            $ue->setEmail($email);
            $ue->setType('alerts');
            $ue->save();
            $ue = null;

            $ue = new UsersEmail();
            $ue->setUserId($user->getId());
            $ue->setEmail($email);
            $ue->setType('billing');
            $ue->save();
            $ue = null;

            $this->session->set('auth', [
                'user' => $user
            ]);

            // Log message
            $message = sprintf('User with email [%s] has successfully registered.', $email);
            $this->logger->info($message);

            // by default subscribe to all Councils
            Councils::subscribeToAll($user->getId());

            // create sample poi for monitor users
            if($solution == 'monitor'){
                \Aiden\Models\Poi::createSamplePoiAction($user->getId());
            }

            // send email notification to admin

            \Aiden\Models\Email::signupNotification($user);
            \Aiden\Models\Email::welcomeNotification($email, $name, $lname, $verificationCode);


            // Append User data into Google Sheets
            \Aiden\Models\GoogleSheets::appendUsers($user, \Aiden\Models\GoogleSheets::getClient());


            if($solution == 'search'){
                return $this->response->redirect('search', false, 302);
            }else{
               return $this->response->redirect('dashboard', false, 302);
            }

        }
        else {

            // Client message
            $errorMessage = 'Something went wrong, please try again.';
            $this->flashSession->error($errorMessage);

            // Log message
            $message = sprintf('Could not signup user [%s]. (Model error: %s)', $email, print_r($user->getMessages(), true));
            $this->logger->error($message);

            return $this->response->redirect('signup', false, 302);
        }

    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return time().$randomString;
    }
}
