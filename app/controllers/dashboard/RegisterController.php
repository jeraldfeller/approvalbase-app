<?php

namespace Aiden\Controllers;

class RegisterController extends _BaseController {

    public function indexAction() {

        // If user is already logged in
        if ($this->session->has('auth')) {
            return $this->response->redirect('', false, 302);
        }

        $this->view->setVars([
            'form' => new \Aiden\Forms\RegisterForm()
        ]);

    }

    public function doAction() {

        // Check if POST request.
        if ($this->request->isPost() === false) {

            $this->flashSession->error('Invalid request');
            return $this->response->redirect($this->dispatcher->getControllerName(), false, 302);
        }

        // Validate the form
        $form = new \Aiden\Forms\RegisterForm();
        if (!$form->isValid($this->request->getPost())) {

            foreach ($form->getMessages() as $message) {

                $this->flashSession->error($message);
                return $this->response->redirect($this->dispatcher->getControllerName(), false, 302);
            }
        }

        $name = $this->request->getPost("name", "string");
        $email = $this->request->getPost("email", "string");
        $password = $this->request->getPost("password");

        $user = new \Aiden\Models\Users();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPasswordHash($this->security->hash($password));
        $user->setLevel(\Aiden\Models\Users::LEVEL_USER);
        $user->setCreated(new \DateTime());
        $user->setLastLogin(new \DateTime());
        $user->setImageUrl("images/avatars/aiden-anonymous.jpg");

        if ($user->save()) {

            $this->session->set('auth', [
                'user' => $user
            ]);

            // Log message
            $message = sprintf('User with email [%s] has successfully registered.', $email);
            $this->logger->info($message);

            return $this->response->redirect('', false, 302);
        }
        else {

            // Client message
            $errorMessage = 'Something went wrong, please try again.';
            $this->flashSession->error($errorMessage);

            // Log message
            $message = sprintf('Could not signup user [%s]. (Model error: %s)', $email, print_r($user->getMessages(), true));
            $this->logger->error($message);

            return $this->response->redirect($this->dispatcher->getControllerName(), false, 302);
        }

    }

}
