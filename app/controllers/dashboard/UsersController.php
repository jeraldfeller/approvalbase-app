<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 5/27/2019
 * Time: 12:11 PM
 */

namespace Aiden\Controllers;


use Aiden\Models\Users;

class UsersController extends _BaseController
{
    public function forgotPasswordIndexAction(){
        $this->view->setVars([
            'page_title' => 'Forgot Password',
            'usersId' => 0
        ]);
        $this->view->pick('forgot-password/index');
    }

    public function changePasswordRequestIndexAction(){
        $vc = $this->request->getQuery('vc');
        $usersId = $this->request->getQuery('a');
        if($usersId && $vc){
            $user = Users::findFirst([
               'conditions' => 'id = :id: AND verification_code = :vc:',
               'bind' => [
                   'id' => $usersId,
                   'vc' => $vc
               ]
            ]);

            if($user){
                $this->view->setVars([
                    'page_title' => 'Change Password',
                    'email' => $user->getEmail(),
                    'usersId' => $user->getId()
                ]);
                $user = null;
                $this->view->pick('forgot-password/change-password');
            }else{
                $this->flashSession->notice('Please login to continue.');
                $this->response->redirect('login', false, 302);
                $this->view->disable();
                return false;
            }
        }else{
            $this->flashSession->notice('Please login to continue.');
            $this->response->redirect('login', false, 302);
            $this->view->disable();
            return false;
        }

    }

    public function changePasswordConfirmAction(){
        $usersId = $this->request->getPost('id');
        $password = $this->request->getPost('password');
        $user = Users::findFirst([
           'conditions' => 'id = :id:',
           'bind' => [
               'id' => $usersId
           ]
        ]);
        if($user){
            $user->setPasswordHash($this->security->hash($password));
            $user->setVerificationCode('');
            $user->save();
            $rsp = json_encode([
                'success' => true,
                'message' => 'Password successfully updated.'
            ]);
        }else{
            $rsp = json_encode([
               'message' => 'Ops! something went wrong, please try again later.',
                'success' => false
            ]);
        }

        $user = null;
        return $rsp;
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
            $user->setVerificationCode($verificationCode);
            $user->save();
            $return =   \Aiden\Models\Email::forgotPasswordEmail($email, $user->getName(), $verificationCode, $user->getId());
            if($return){

                $rsp = json_encode([
                   'success' => true,
                   'message' => 'If an ApprovalBase email exists for '.$email.', an e-mail will be sent with further instructions.'
                ]);
            }else{
                $rsp = json_encode([
                    'success' => false,
                    'message' => 'Ops. Something went wrong, please try again later.'
                ]);
            }

        }else{
            $rsp = json_encode([
              'success' => false,
              'message' => 'Email does not exist, please try again.'
            ]);
        }

        $return = null;
        $user = null;

        return $rsp;

    }




}