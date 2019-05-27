<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 5/27/2019
 * Time: 2:26 PM
 */

namespace Common\Controllers;


class UsersControllers extends _BaseController
{
    public function forgotPasswordIndexAction(){
        $this->view->setVars([
            'page_title' => 'Forgot Password',
        ]);
        $this->view->pick('forgot-password/index');
    }

    public function changePasswordIndexAction(){
        $vc = $this->request->getQuery('vc');
        $this->view->setVars([
            'page_title' => 'Change Password',
            'email' => $vc
        ]);
        $this->view->pick('forgot-password/change-password');
    }

    public function sendForgotPasswordEmailAction(){
        $email = trim($this->request->getPost('email'));
        $user = Users::findFirst([
            'conditions' => 'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if($user){
            $verificationCode = $this->generateRandomString();
            $return =   \Aiden\Models\Email::forgotPasswordEmail($email, $user->getName(), $verificationCode);

            if($return){
                return json_encode([
                    'success' => true,
                    'message' => 'If a ApprovalBase account exists for '.$email.', an e-mail will be sent with further instructions.'
                ]);
            }else{
                return json_encode([
                    'success' => false,
                    'message' => 'Ops. Something went wrong, please try again later.'
                ]);
            }

        }else{
            return json_encode([
                'success' => false,
                'message' => 'Email does not exist, please try again.'
            ]);
        }

    }
}