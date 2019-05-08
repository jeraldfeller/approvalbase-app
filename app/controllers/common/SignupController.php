<?php

namespace Common\Controllers;

class SignupController extends _BaseController {

    public function indexAction() {

        if ($this->session->has('auth')) {
            return $this->response->redirect('dashboard', false, 302);
        }

        $this->view->setVars([
            'form' => new \Aiden\Forms\RegisterForm(),
            'user'  => $this->getUser(),
            'page_title' => 'Sign Up Free Trial Account | ApprovalBase'
        ]);

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
                return $this->response->redirect('/#form-response', 302);
            }
        }

        $name = $this->request->getPost("name", "string");
        $email = $this->request->getPost("email", "string");
        $password = $this->request->getPost("password");
        $websiteUrl = $this->request->getPost("websiteUrl", "string");
        $companyName = $this->request->getPost("companyName", "string");
        $companyCountry = $this->request->getPost("companyCountry", "string");
        $companyCity = $this->request->getPost("companyCity", "string");
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
        $user = new \Aiden\Models\Users();
        $user->setName($name);
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

        if ($user->save()) {

            $this->session->set('auth', [
                'user' => $user
            ]);

            // Log message
            $message = sprintf('User with email [%s] has successfully registered.', $email);
            $this->logger->info($message);

            return $this->response->redirect('leads', false, 302);
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
}
